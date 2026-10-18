<?php

namespace App\Controllers\Admin;

use App\Libraries\SeoAudit;
use App\Models\MediaModel;
use App\Models\PageModel;
use App\Models\SeoSettingModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * SEO: how every page appears in Google, the site-wide settings behind link
 * previews and search engines, and the list of pages Google is told about.
 *
 * Three tabs, so each job is on its own screen:
 *   pages     the checklist — what needs fixing, with a Google-style preview
 *   settings  site-wide switches, grouped by what they do
 *   sitemap   what search engines are told
 *
 * Per-page titles and descriptions are edited in Pages and Machines; this
 * screen reports on them and links across.
 */
class Seo extends AdminController
{
    private const TABS = ['pages', 'settings', 'sitemap'];

    public function index(): string
    {
        $tab = (string) ($this->request->getGet('tab') ?? 'pages');
        $tab = in_array($tab, self::TABS, true) ? $tab : 'pages';

        $audit   = new SeoAudit();
        $all     = $audit->rows();
        $summary = $audit->summary($all);

        // Problems first, so the list starts with what needs doing.
        $needsWork = array_values(array_filter($all, static fn ($r) => $r['issues'] !== []));
        $good      = array_values(array_filter($all, static fn ($r) => $r['issues'] === []));

        $show = (string) ($this->request->getGet('show') ?? ($needsWork !== [] ? 'issues' : 'all'));
        $show = in_array($show, ['issues', 'good', 'all'], true) ? $show : 'all';
        $rows = match ($show) {
            'issues' => $needsWork,
            'good'   => $good,
            default  => array_merge($needsWork, $good),
        };

        $pager = $this->listPage(count($rows));

        // The domain as Google prints it above a result: www.example.com › about
        $base   = SeoSettingModel::canonicalBase() ?: preg_replace('#/index\.php$#', '', rtrim(base_url(), '/'));
        $domain = (string) preg_replace('#^https?://#', '', (string) $base);

        $sitemap = array_map(static fn ($u) => [
            'url'     => $base . ($u['loc'] === '/' ? '/' : $u['loc']),
            'path'    => $u['loc'],
            'lastmod' => $u['lastmod'],
        ], $audit->sitemapUrls());

        return $this->adminView('admin/seo/index', [
            'pageTitle'    => 'SEO',
            'tab'          => $tab,
            'show'         => $show,
            'rows'         => $pager->slice($rows),
            'pager'        => $pager,
            'summary'      => $summary,
            'settings'     => SeoSettingModel::all(),
            'fields'       => SeoSettingModel::FIELDS,
            'canEdit'      => ($this->adminUser['role'] ?? 'editor') === 'admin',
            'images'       => array_keys(timb_manifest()),
            'domain'       => $domain,
            'siteName'     => config('Site')->name,
            // The home page's title, as the example in the link-preview mock-up.
            'shareTitle'   => $this->homeTitle(),
            'limits'       => [
                'titleMin'       => SeoAudit::TITLE_MIN,
                'titleMax'       => SeoAudit::TITLE_MAX,
                'descriptionMin' => SeoAudit::DESCRIPTION_MIN,
                'descriptionMax' => SeoAudit::DESCRIPTION_MAX,
            ],
            'sitemap'      => $sitemap,
            'sitemapUrl'   => base_url('sitemap.xml'),
            'robotsUrl'    => base_url('robots.txt'),
            'staticRobots' => is_file(FCPATH . 'robots.txt'),
            'indexingOff'  => ! SeoSettingModel::on('allow_indexing'),
            'photos'       => $this->photoDescriptions(),
        ]);
    }

    /** How many library photos have a description, for the Google Images tip. */
    private function photoDescriptions(): array
    {
        try {
            $rows = (new MediaModel())->select('title, alt_text')->findAll();
        } catch (\Throwable $e) {
            log_message('error', 'SEO screen could not read the media library: ' . $e->getMessage());

            return ['total' => 0, 'missing' => 0];
        }

        $missing = count(array_filter($rows, static fn ($r) => MediaModel::description($r) === ''));

        return ['total' => count($rows), 'missing' => $missing];
    }

    private function homeTitle(): string
    {
        try {
            [$title] = (new PageModel())->seo('home');
        } catch (\Throwable $e) {
            log_message('error', 'SEO screen could not read the home title: ' . $e->getMessage());
            $title = null;
        }

        return $title ?? config('Site')->name;
    }

    public function update(): RedirectResponse
    {
        $this->requireRole('admin');

        SeoSettingModel::save($this->request->getPost());
        $this->audit('seo_settings_update', 'SEO settings', null, 'seo');

        return redirect()->to('/admin/seo?tab=settings')->with('success', 'SEO settings saved.');
    }
}
