<?php

namespace App\Controllers\Admin;

use App\Libraries\SeoAudit;
use App\Models\ApplicationModel;
use App\Models\EnquiryModel;
use App\Models\MachineModel;
use App\Models\MediaModel;
use App\Models\NewsModel;
use App\Models\PageModel;
use App\Models\RedirectModel;
use App\Models\UserModel;

/**
 * The dashboard: what needs attention, what the site currently holds, and
 * what changed recently.
 *
 * Every figure is gathered defensively — one unreadable table leaves its own
 * panel empty rather than taking the whole screen down.
 */
class Dashboard extends AdminController
{
    public function index(): string
    {
        return $this->adminView('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'inbox'     => $this->inbox(),
            'content'   => $this->content(),
            'attention' => $this->attention(),
            'recent'    => $this->recentMessages(),
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
                'enquiriesNew'    => $enquiries['new'],
                'enquiriesTotal'  => $enquiries['all'],
                'applicationsNew' => $applications['new'],
                'applicationsAll' => $applications['all'],
            ];
        }, ['enquiriesNew' => 0, 'enquiriesTotal' => 0, 'applicationsNew' => 0, 'applicationsAll' => 0]);
    }

    /** Headline counts, each with how many are not live. */
    private function content(): array
    {
        $machines = $this->safely(static function () {
            $m   = new MachineModel();
            $all = $m->findAll();

            return [
                'total' => count($all),
                'draft' => count(array_filter($all, static fn ($r) => ! $r['is_published'])),
            ];
        }, ['total' => 0, 'draft' => 0]);

        $news = $this->safely(static function () {
            $n   = new NewsModel();
            $all = $n->findAll();

            return [
                'total' => count($all),
                'draft' => count(array_filter($all, static fn ($r) => ! $r['is_published'])),
            ];
        }, ['total' => 0, 'draft' => 0]);

        $pages = $this->safely(static fn () => count((new PageModel())->forAdmin()), 0);

        $media = $this->safely(static function () {
            $model  = new MediaModel();
            $rows   = $model->allOrdered();
            $usage  = $model->usage();
            $unused = 0;
            foreach ($rows as $r) {
                if (($usage[$r['legacy_key']] ?? []) === []) {
                    $unused++;
                }
            }

            return ['total' => count($rows), 'unused' => $unused];
        }, ['total' => 0, 'unused' => 0]);

        $redirects = $this->safely(static function () {
            $rows = (new RedirectModel())->allOrdered();

            return ['total' => count($rows), 'hits' => array_sum(array_column($rows, 'hits'))];
        }, ['total' => 0, 'hits' => 0]);

        return compact('machines', 'news', 'pages', 'media', 'redirects');
    }

    /**
     * Things worth acting on, most pressing first. Each is [text, link, tone].
     * An empty list means there is genuinely nothing outstanding.
     */
    private function attention(): array
    {
        $items = [];
        $inbox = $this->inbox();

        if ($inbox['enquiriesNew'] > 0) {
            $items[] = [
                'text' => $inbox['enquiriesNew'] . ' unread ' . ($inbox['enquiriesNew'] === 1 ? 'enquiry' : 'enquiries'),
                'link' => '/admin/inbox?status=new',
                'tone' => 'red',
            ];
        }
        if ($inbox['applicationsNew'] > 0) {
            $items[] = [
                'text' => $inbox['applicationsNew'] . ' new job ' . ($inbox['applicationsNew'] === 1 ? 'application' : 'applications'),
                'link' => '/admin/inbox/applications?status=new',
                'tone' => 'red',
            ];
        }

        $content = $this->content();
        if ($content['machines']['draft'] > 0) {
            $items[] = [
                'text' => $content['machines']['draft'] . ' machine' . ($content['machines']['draft'] === 1 ? '' : 's') . ' not published',
                'link' => '/admin/machines',
                'tone' => 'orange',
            ];
        }
        if ($content['news']['draft'] > 0) {
            $items[] = [
                'text' => $content['news']['draft'] . ' news article' . ($content['news']['draft'] === 1 ? '' : 's') . ' still a draft',
                'link' => '/admin/news',
                'tone' => 'orange',
            ];
        }

        $seoIssues = $this->safely(static function () {
            $audit = new SeoAudit();

            return $audit->summary($audit->rows())['issues'];
        }, 0);
        if ($seoIssues > 0) {
            $items[] = [
                'text' => $seoIssues . ' page' . ($seoIssues === 1 ? '' : 's') . ' with a title or description worth a look',
                'link' => '/admin/seo',
                'tone' => 'orange',
            ];
        }

        $locked = $this->safely(static function () {
            $model = new UserModel();

            return count(array_filter($model->listing(), static fn ($u) => $model->isLocked($u)));
        }, 0);
        if ($locked > 0) {
            $items[] = [
                'text' => $locked . ' locked ' . ($locked === 1 ? 'account' : 'accounts'),
                'link' => '/admin/users',
                'tone' => 'red',
            ];
        }

        if (trim((string) (config('Email')->SMTPHost ?? '')) === '') {
            $items[] = [
                'text' => 'Email is not set up, so nobody is told when an enquiry arrives',
                'link' => '/admin/settings',
                'tone' => 'orange',
            ];
        }

        if (is_file(FCPATH . 'robots.txt')) {
            $items[] = [
                'text' => 'A robots.txt file in the public folder is overriding the one the CMS generates',
                'link' => '/admin/seo',
                'tone' => 'orange',
            ];
        }

        return $items;
    }

    /** The five newest messages across both inbox tabs. */
    private function recentMessages(): array
    {
        return $this->safely(static function () {
            $rows = [];

            foreach ((new EnquiryModel())->withStatus(null) as $r) {
                $rows[] = [
                    'kind'    => 'Enquiry',
                    'name'    => $r['name'],
                    'detail'  => trim((string) $r['company']) !== '' ? $r['company'] : $r['email'],
                    'at'      => $r['created_at'],
                    'new'     => $r['status'] === 'new',
                    'link'    => '/admin/inbox/message/enquiry/' . $r['id'],
                ];
            }
            foreach ((new ApplicationModel())->withStatus(null) as $r) {
                $rows[] = [
                    'kind'   => 'Application',
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

    /** Recent entries from the audit log. */
    private function activity(): array
    {
        return $this->safely(static fn () => db_connect()->table('audit_log')
            ->select('action, entity, summary, user_id, created_at')
            ->orderBy('id', 'DESC')->limit(8)->get()->getResultArray(), []);
    }
}
