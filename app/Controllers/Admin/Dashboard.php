<?php

namespace App\Controllers\Admin;

use App\Libraries\Notifier;
use App\Libraries\SeoAudit;
use App\Models\ApplicationModel;
use App\Models\EnquiryModel;
use App\Models\ListItemModel;
use App\Models\MachineModel;
use App\Models\MediaModel;
use App\Models\NewsModel;
use App\Models\PageModel;
use App\Models\UserModel;

/**
 * The dashboard, written for someone who edits the website rather than builds
 * it: a short list of things to do (each saying why it matters and what to
 * press), the sections of the site with what each one controls, the common
 * tasks, new messages, and who changed what.
 *
 * Every figure is gathered defensively — one unreadable table leaves its own
 * panel empty rather than taking the whole screen down.
 */
class Dashboard extends AdminController
{
    public function index(): string
    {
        $isAdmin = $this->isAdmin();
        $inbox   = $this->inbox();
        $content = $this->content();

        // Job applications carry CVs and are admin-only; editors see enquiries.
        if (! $isAdmin) {
            $inbox['applicationsNew'] = 0;
            $inbox['applicationsAll'] = 0;
        }

        return $this->adminView('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'todo'      => $this->todo($inbox, $content, $isAdmin),
            'sections'  => $this->sections($inbox, $content),
            'tasks'     => $this->tasks($isAdmin),
            'recent'    => $this->recentMessages($isAdmin),
            'activity'  => $this->activity(),
        ]);
    }

    /** Run a closure, returning $fallback if the table is unreadable. */
    private function safely(callable $fn, $fallback)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard panel unavailable: ' . $e->getMessage());

            return $fallback;
        }
    }

    private function inbox(): array
    {
        return $this->safely(static function () {
            $enquiries    = (new EnquiryModel())->counts();
            $applications = (new ApplicationModel())->counts();

            return [
                'enquiriesNew'    => (int) $enquiries['new'],
                'enquiriesAll'    => (int) $enquiries['all'],
                'applicationsNew' => (int) $applications['new'],
                'applicationsAll' => (int) $applications['all'],
            ];
        }, ['enquiriesNew' => 0, 'enquiriesAll' => 0, 'applicationsNew' => 0, 'applicationsAll' => 0]);
    }

    /** Headline counts, each with how many are not live. */
    private function content(): array
    {
        $liveAndDraft = function (string $model): array {
            return $this->safely(static function () use ($model) {
                $all = (new $model())->findAll();

                return [
                    'total' => count($all),
                    'draft' => count(array_filter($all, static fn ($r) => ! (int) $r['is_published'])),
                ];
            }, ['total' => 0, 'draft' => 0]);
        };

        return [
            'machines' => $liveAndDraft(MachineModel::class),
            'news'     => $liveAndDraft(NewsModel::class),
            'pages'    => $this->safely(static fn () => count((new PageModel())->forAdmin()), 0),
            'media'    => $this->safely(static fn () => count((new MediaModel())->allOrdered()), 0),
            'lists'    => $this->safely(static fn () => count(ListItemModel::definitions()), 0),
            'seo'      => $this->safely(static function () {
                $audit = new SeoAudit();

                return (int) $audit->summary($audit->rows())['issues'];
            }, 0),
            'locked'   => $this->safely(static function () {
                $model = new UserModel();

                return count(array_filter($model->listing(), static fn ($u) => $model->isLocked($u)));
            }, 0),
        ];
    }

    /**
     * Things worth acting on, most pressing first. Each says what it is, why
     * it matters and what the button does. level: urgent | suggested.
     * An empty list means there is genuinely nothing outstanding.
     */
    private function todo(array $inbox, array $content, bool $isAdmin): array
    {
        $items = [];
        $s     = static fn (int $n, string $one, string $many) => $n . ' ' . ($n === 1 ? $one : $many);

        if ($inbox['enquiriesNew'] > 0) {
            $items[] = [
                'level'  => 'urgent',
                'icon'   => 'mail',
                'title'  => $s($inbox['enquiriesNew'], 'new enquiry is', 'new enquiries are') . ' waiting',
                'why'    => 'Sent by visitors through the Contact page. They are expecting a reply.',
                'action' => 'Read ' . ($inbox['enquiriesNew'] === 1 ? 'it' : 'them'),
                'link'   => '/admin/inbox?status=new',
            ];
        }
        if ($inbox['applicationsNew'] > 0) {
            $items[] = [
                'level'  => 'urgent',
                'icon'   => 'person',
                'title'  => $s($inbox['applicationsNew'], 'new job application', 'new job applications'),
                'why'    => 'Sent through the Careers page, with a CV attached.',
                'action' => 'Read ' . ($inbox['applicationsNew'] === 1 ? 'it' : 'them'),
                'link'   => '/admin/inbox/applications?status=new',
            ];
        }
        if ($isAdmin && $content['locked'] > 0) {
            $items[] = [
                'level'  => 'urgent',
                'icon'   => 'lock',
                'title'  => $s($content['locked'], 'user account is', 'user accounts are') . ' locked',
                'why'    => 'Too many wrong passwords. The person cannot sign in until it is unlocked or 15 minutes pass.',
                'action' => 'Open users',
                'link'   => '/admin/users',
            ];
        }
        if ($content['machines']['draft'] > 0) {
            $items[] = [
                'level'  => 'suggested',
                'icon'   => 'eye',
                'title'  => $s($content['machines']['draft'], 'machine is', 'machines are') . ' hidden from the website',
                'why'    => 'Saved, but visitors cannot see ' . ($content['machines']['draft'] === 1 ? 'it' : 'them')
                          . '. Tick “Published” when ready to show.',
                'action' => 'Review machines',
                'link'   => '/admin/machines',
            ];
        }
        if ($content['news']['draft'] > 0) {
            $items[] = [
                'level'  => 'suggested',
                'icon'   => 'eye',
                'title'  => $s($content['news']['draft'], 'news article is', 'news articles are') . ' still a draft',
                'why'    => 'Drafts do not appear on the Latest News page until published.',
                'action' => 'Review news',
                'link'   => '/admin/news',
            ];
        }
        $mail = Notifier::status();
        if (! $mail['ok']) {
            $items[] = [
                'level'  => 'suggested',
                'icon'   => 'bell',
                'title'  => 'Email alerts are not switched on',
                'why'    => 'New messages are still saved here, but nobody gets an email when one arrives. ' . $mail['problem'],
                'action' => $isAdmin ? 'See details' : null,
                'link'   => $isAdmin ? '/admin/settings#email' : null,
            ];
        }
        if ($content['seo'] > 0) {
            $items[] = [
                'level'  => 'suggested',
                'icon'   => 'search',
                'title'  => $s($content['seo'], 'page could', 'pages could') . ' look better in Google',
                'why'    => 'The title or description shown in search results is too short, too long or repeated.',
                'action' => 'Fix in SEO',
                'link'   => '/admin/seo',
            ];
        }
        if (is_file(FCPATH . 'robots.txt')) {
            $items[] = [
                'level'  => 'suggested',
                'icon'   => 'search',
                'title'  => 'An old robots.txt file is overriding your search settings',
                'why'    => 'Ask your developer to delete public/robots.txt on the server, so the settings in SEO take effect.',
                'action' => 'Open SEO',
                'link'   => '/admin/seo',
            ];
        }

        return $items;
    }

    /** The parts of the website, each with what it controls and how it stands. */
    private function sections(array $inbox, array $content): array
    {
        $new   = $inbox['enquiriesNew'] + $inbox['applicationsNew'];
        $total = $inbox['enquiriesAll'] + $inbox['applicationsAll'];
        $m     = $content['machines'];
        $n     = $content['news'];

        return [
            [
                'icon'  => 'mail',
                'count' => $new,
                'label' => $new === 1 ? 'New message' : 'New messages',
                'desc'  => 'Enquiries and job applications sent from the website.',
                'note'  => ($new > 0 ? 'Waiting for a reply' : 'Nothing waiting') . ' · ' . $total . ' received so far',
                'state' => $new > 0 ? 'warn' : 'ok',
                'link'  => '/admin/inbox',
            ],
            [
                'icon'  => 'gear',
                'count' => $m['total'] - $m['draft'],
                'label' => 'Machines live',
                'desc'  => 'Products shown on the website and in the Products menu.',
                'note'  => $m['draft'] ? $m['draft'] . ' hidden' : 'All visible',
                'state' => $m['draft'] ? 'warn' : 'ok',
                'link'  => '/admin/machines',
            ],
            [
                'icon'  => 'news',
                'count' => $n['total'] - $n['draft'],
                'label' => 'News articles live',
                'desc'  => 'Posts on the Latest News page.',
                'note'  => $n['draft'] ? $n['draft'] . ' draft' . ($n['draft'] === 1 ? '' : 's') : 'All published',
                'state' => $n['draft'] ? 'warn' : 'ok',
                'link'  => '/admin/news',
            ],
            [
                'icon'  => 'page',
                'count' => $content['pages'],
                'label' => 'Pages',
                'desc'  => 'Text on About, Careers, Contact and the other pages.',
                'note'  => null,
                'state' => 'ok',
                'link'  => '/admin/pages',
            ],
            [
                'icon'  => 'image',
                'count' => $content['media'],
                'label' => 'Photos',
                'desc'  => 'Pictures you can use on machines and news.',
                'note'  => null,
                'state' => 'ok',
                'link'  => '/admin/media',
            ],
            [
                'icon'  => 'list',
                'count' => $content['lists'],
                'label' => 'Lists',
                'desc'  => 'Short lists: figures, FAQ, clients, locations and more.',
                'note'  => null,
                'state' => 'ok',
                'link'  => '/admin/lists',
            ],
        ];
    }

    /** The jobs people come here to do, in plain words. */
    private function tasks(bool $isAdmin): array
    {
        $tasks = [
            ['icon' => 'news',  'title' => 'Write a news article', 'desc' => 'Post an update to Latest News.',         'link' => '/admin/news/create'],
            ['icon' => 'gear',  'title' => 'Add a machine',        'desc' => 'Put a new product on the website.',      'link' => '/admin/machines/create'],
            ['icon' => 'image', 'title' => 'Upload a photo',       'desc' => 'Add a picture to the photo library.',    'link' => '/admin/media'],
            ['icon' => 'page',  'title' => 'Change page text',     'desc' => 'Edit About, Careers and other pages.',   'link' => '/admin/pages'],
            ['icon' => 'list',  'title' => 'Update a list',        'desc' => 'FAQ, clients, figures, locations.',      'link' => '/admin/lists'],
        ];

        if ($isAdmin) {
            $tasks[] = ['icon' => 'settings', 'title' => 'Site name & contact', 'desc' => 'Email address, LinkedIn, footer.', 'link' => '/admin/settings'];
        }

        return $tasks;
    }

    /** The five newest messages across both inbox tabs. */
    private function recentMessages(bool $withApplications): array
    {
        return $this->safely(static function () use ($withApplications) {
            $rows = [];

            foreach ((new EnquiryModel())->withStatus(null) as $r) {
                $rows[] = [
                    'kind'   => 'Enquiry',
                    'name'   => $r['name'],
                    'detail' => trim((string) $r['company']) !== '' ? $r['company'] : $r['email'],
                    'at'     => $r['created_at'],
                    'new'    => $r['status'] === 'new',
                    'link'   => '/admin/inbox/message/enquiry/' . $r['id'],
                ];
            }
            foreach ($withApplications ? (new ApplicationModel())->withStatus(null) : [] as $r) {
                $rows[] = [
                    'kind'   => 'Job application',
                    'name'   => $r['name'],
                    'detail' => $r['area'],
                    'at'     => $r['created_at'],
                    'new'    => $r['status'] === 'new',
                    'link'   => '/admin/inbox/message/application/' . $r['id'],
                ];
            }

            usort($rows, static fn ($a, $b) => strcmp((string) $b['at'], (string) $a['at']));

            return array_slice($rows, 0, 5);
        }, []);
    }

    /**
     * Recent edits from the audit log, with who made them. Signing in and out
     * is left out: it is most of the log and says nothing about the website.
     */
    private function activity(): array
    {
        return $this->safely(static fn () => db_connect()->table('audit_log a')
            ->select('a.action, a.summary, a.created_at, u.name AS user_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->whereNotIn('a.action', ['login', 'logout', 'inbox_cv_download'])
            ->orderBy('a.id', 'DESC')->limit(6)->get()->getResultArray(), []);
    }
}
