<?php

namespace App\Controllers;

use App\Libraries\SeoAudit;
use App\Models\SeoSettingModel;

/**
 * The two files search engines ask for. Both are generated, so they stay in
 * step with what the CMS actually publishes.
 */
class Seo extends BaseController
{
    public function sitemap()
    {
        $base = SeoSettingModel::canonicalBase() ?: rtrim(base_url(), '/');
        $urls = (new SeoAudit())->sitemapUrls();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $loc = $base . ($u['loc'] === '/' ? '/' : $u['loc']);
            $xml .= "  <url>\n    <loc>" . esc($loc, 'raw') . "</loc>\n";
            if ($u['lastmod'] !== null) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>' . "\n";

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    public function robots()
    {
        $allow = SeoSettingModel::on('allow_indexing');
        $base  = SeoSettingModel::canonicalBase() ?: rtrim(base_url(), '/');

        $lines = ['User-agent: *'];

        if ($allow) {
            $lines[] = 'Disallow: /admin/';
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $base . '/sitemap.xml';
        } else {
            // Asked to stay away entirely; pointing at a sitemap would undo it.
            $lines[] = 'Disallow: /';
        }

        $extra = trim(SeoSettingModel::get('robots_extra'));
        if ($extra !== '') {
            $lines[] = '';
            foreach (preg_split('/\R/', $extra) ?: [] as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        return $this->response->setContentType('text/plain')->setBody(implode("\n", $lines) . "\n");
    }
}
