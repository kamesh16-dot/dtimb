<?php

namespace App\Controllers\Admin;

use App\Libraries\SeoAudit;
use App\Models\SeoSettingModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * SEO: what search engines see for every address the site serves, plus the
 * site-wide settings behind the sharing previews, canonical links, sitemap
 * and robots.txt.
 *
 * Per-page titles and descriptions are edited in Pages; this screen reports
 * on them and links across.
 */
class Seo extends AdminController
{
    public function index(): string
    {
        $audit = new SeoAudit();
        $rows  = $audit->rows();

        // Shown as a table on the screen. The raw file is a click further
        // away: it is written for search engines, not for an editor to read.
        $base    = SeoSettingModel::canonicalBase() ?: rtrim(base_url(), '/');
        $sitemap = array_map(static fn ($u) => [
            'url'     => $base . ($u['loc'] === '/' ? '/' : $u['loc']),
            'path'    => $u['loc'],
            'lastmod' => $u['lastmod'],
        ], $audit->sitemapUrls());

        return $this->adminView('admin/seo/index', [
            'pageTitle' => 'SEO',
            'rows'      => $rows,
            'summary'   => $audit->summary($rows),
            'settings'  => SeoSettingModel::all(),
            'fields'    => SeoSettingModel::FIELDS,
            'limits'    => [
                'titleMax'       => SeoAudit::TITLE_MAX,
                'descriptionMax' => SeoAudit::DESCRIPTION_MAX,
            ],
            'sitemap'    => $sitemap,
            'sitemapUrl' => base_url('sitemap.xml'),
            'robotsUrl'  => base_url('robots.txt'),
            'staticRobots' => is_file(FCPATH . 'robots.txt'),
        ]);
    }

    public function update(): RedirectResponse
    {
        $this->requireRole('admin');

        SeoSettingModel::save($this->request->getPost());
        $this->audit('seo_settings_update', 'SEO settings', null, 'seo');

        return redirect()->to('/admin/seo')->with('success', 'SEO settings saved.');
    }
}
