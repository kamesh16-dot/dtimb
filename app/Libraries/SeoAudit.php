<?php

namespace App\Libraries;

use App\Models\MachineModel;
use App\Models\NewsModel;
use App\Models\PageModel;

/**
 * Checks every public address the site serves and reports what search engines
 * would see: the browser title, the description, and anything likely to hurt.
 *
 * The thresholds are the usual display limits — titles are cut around 60
 * characters and descriptions around 160 — not hard rules, so everything here
 * is worded as advice.
 */
class SeoAudit
{
    public const TITLE_MAX       = 60;
    // Low enough that a short brand name is not flagged: "About | TIMB" is a
    // perfectly good title at 12 characters. This catches a near-empty one.
    public const TITLE_MIN       = 10;
    public const DESCRIPTION_MAX = 160;
    public const DESCRIPTION_MIN = 70;

    /**
     * One entry per public address.
     *
     * @return list<array{url:string,label:string,title:string,description:string,editUrl:?string,issues:list<string>}>
     */
    public function rows(): array
    {
        $rows = array_merge($this->pages(), $this->machines());
        $this->flagDuplicates($rows);

        return $rows;
    }

    /** Totals for the summary band. */
    public function summary(array $rows): array
    {
        $withIssues = array_filter($rows, static fn ($r) => $r['issues'] !== []);

        return [
            'total'  => count($rows),
            'clean'  => count($rows) - count($withIssues),
            'issues' => count($withIssues),
        ];
    }

    // ── Sources ───────────────────────────────────────────────────────────────

    private function pages(): array
    {
        $rows = [];

        try {
            foreach ((new PageModel())->forAdmin() as $p) {
                $key   = (string) $p['key'];
                $rows[] = $this->row(
                    $key === 'home' ? '/' : '/' . $key,
                    $p['title'],
                    (string) $p['seo_title'],
                    (string) ($p['seo_description'] ?? ''),
                    '/admin/pages/edit/' . $key
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'SEO audit could not read pages: ' . $e->getMessage());
        }

        return $rows;
    }

    private function machines(): array
    {
        $rows = [];

        try {
            $machines = new MachineModel();
            $site     = config('Site');

            foreach ($machines->published() as $m) {
                $slug    = (string) $m['slug'];
                $details = $machines->details($slug) ?? [];

                $rows[] = $this->row(
                    '/machines/' . $slug,
                    $m['name'],
                    $m['name'] . ' | ' . $site->name,
                    (string) ($details['tagline'] ?? ''),
                    '/admin/machines/edit/' . $m['id']
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'SEO audit could not read machines: ' . $e->getMessage());
        }

        return $rows;
    }

    // ── Checks ────────────────────────────────────────────────────────────────

    private function row(string $url, string $label, string $title, string $description, ?string $editUrl): array
    {
        return [
            'url'         => $url,
            'label'       => $label,
            'title'       => $title,
            'description' => $description,
            'editUrl'     => $editUrl,
            'issues'      => $this->issues($title, $description),
        ];
    }

    /** @return list<string> */
    private function issues(string $title, string $description): array
    {
        $issues = [];
        $t      = mb_strlen(trim($title));
        $d      = mb_strlen(trim($description));

        if ($t === 0) {
            $issues[] = 'No browser title.';
        } elseif ($t > self::TITLE_MAX) {
            $issues[] = 'Title is ' . $t . ' characters; search results cut off around ' . self::TITLE_MAX . '.';
        } elseif ($t < self::TITLE_MIN) {
            $issues[] = 'Title is very short at ' . $t . ' characters; add a word or two.';
        }

        if ($d === 0) {
            $issues[] = 'No search description, so search engines will invent one.';
        } elseif ($d > self::DESCRIPTION_MAX) {
            $issues[] = 'Description is ' . $d . ' characters; search results cut off around ' . self::DESCRIPTION_MAX . '.';
        } elseif ($d < self::DESCRIPTION_MIN) {
            $issues[] = 'Description is short at ' . $d . ' characters.';
        }

        return $issues;
    }

    /** Two pages sharing a title or description compete with each other. */
    private function flagDuplicates(array &$rows): void
    {
        foreach (['title' => 'title', 'description' => 'description'] as $field => $word) {
            $seen = [];
            foreach ($rows as $r) {
                $value = mb_strtolower(trim($r[$field]));
                if ($value !== '') {
                    $seen[$value] = ($seen[$value] ?? 0) + 1;
                }
            }

            foreach ($rows as $i => $r) {
                $value = mb_strtolower(trim($r[$field]));
                if ($value !== '' && ($seen[$value] ?? 0) > 1) {
                    $rows[$i]['issues'][] = 'The same ' . $word . ' is used on another page.';
                }
            }
        }
    }

    // ── Sitemap ───────────────────────────────────────────────────────────────

    /**
     * Every address worth listing in the sitemap, with its last change date.
     *
     * @return list<array{loc:string,lastmod:?string}>
     */
    public function sitemapUrls(): array
    {
        $urls = [];

        try {
            foreach ((new PageModel())->byKey() as $key => $p) {
                $urls[] = [
                    'loc'     => $key === 'home' ? '/' : '/' . $key,
                    'lastmod' => $this->date($p['updated_at'] ?? null),
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Sitemap could not read pages: ' . $e->getMessage());
        }

        try {
            foreach ((new MachineModel())->published() as $m) {
                $urls[] = [
                    'loc'     => '/machines/' . $m['slug'],
                    'lastmod' => $this->date($m['updated_at'] ?? null),
                ];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Sitemap could not read machines: ' . $e->getMessage());
        }

        // The news page is already listed as a page; a newer article moves its date.
        try {
            $articles = (new NewsModel())->published();
            if ($articles !== []) {
                $newest = max(array_map(fn ($a) => (string) ($a['updated_at'] ?? ''), $articles));
                foreach ($urls as $i => $u) {
                    if ($u['loc'] === '/news' && $newest !== '') {
                        $urls[$i]['lastmod'] = $this->date($newest);
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Sitemap could not read news: ' . $e->getMessage());
        }

        return $urls;
    }

    private function date(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $time = strtotime($value);

        return $time === false ? null : date('Y-m-d', $time);
    }
}
