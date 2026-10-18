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
     * `problems` holds each finding with a plain fix; `issues` is the same
     * findings as bare sentences, for callers that only need a count.
     *
     * @return list<array{url:string,label:string,kind:string,title:string,description:string,editUrl:?string,note:?string,issues:list<string>,problems:list<array{part:string,text:string,tip:string}>}>
     */
    public function rows(): array
    {
        $rows = array_merge($this->pages(), $this->machines());
        $this->flagDuplicates($rows);

        foreach ($rows as $i => $r) {
            $rows[$i]['issues'] = array_column($r['problems'], 'text');
        }

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
                    'Page',
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
                    'Machine',
                    $m['name'] . ' | ' . $site->name,
                    (string) ($details['tagline'] ?? ''),
                    '/admin/machines/edit/' . $m['id'],
                    'The title is the machine’s name followed by “| ' . $site->name . '”. The description is the machine’s '
                    . '“Search description” on its edit form, or its summary when that is left blank.'
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'SEO audit could not read machines: ' . $e->getMessage());
        }

        return $rows;
    }

    // ── Checks ────────────────────────────────────────────────────────────────

    private function row(string $url, string $label, string $kind, string $title, string $description, ?string $editUrl, ?string $note = null): array
    {
        return [
            'url'         => $url,
            'label'       => $label,
            'kind'        => $kind,
            'title'       => $title,
            'description' => $description,
            'editUrl'     => $editUrl,
            'note'        => $note,
            'problems'    => $this->problems($title, $description),
            'issues'      => [],
        ];
    }

    /**
     * What is wrong, in plain words, each with how to fix it.
     *
     * @return list<array{part:string,text:string,tip:string}>
     */
    public function problems(string $title, string $description): array
    {
        $out = [];
        $t   = mb_strlen(trim($title));
        $d   = mb_strlen(trim($description));

        if ($t === 0) {
            $out[] = ['part' => 'title', 'text' => 'There is no title, so Google shows the page address instead.',
                      'tip'  => 'Write a short title that names the page, e.g. “About | TIMB”.'];
        } elseif ($t > self::TITLE_MAX) {
            $out[] = ['part' => 'title', 'text' => 'The title is ' . $t . ' characters. Google cuts it off after about ' . self::TITLE_MAX . '.',
                      'tip'  => 'Shorten it, keeping the most important words at the start.'];
        } elseif ($t < self::TITLE_MIN) {
            $out[] = ['part' => 'title', 'text' => 'The title is very short (' . $t . ' characters).',
                      'tip'  => 'Add a word or two that says what the page is about.'];
        }

        if ($d === 0) {
            $out[] = ['part' => 'description', 'text' => 'There is no description, so Google picks some text from the page by itself.',
                      'tip'  => 'Write one or two sentences (' . self::DESCRIPTION_MIN . '–' . self::DESCRIPTION_MAX . ' characters) saying what is on the page.'];
        } elseif ($d > self::DESCRIPTION_MAX) {
            $out[] = ['part' => 'description', 'text' => 'The description is ' . $d . ' characters. Google cuts it off after about ' . self::DESCRIPTION_MAX . '.',
                      'tip'  => 'Trim it to one or two sentences.'];
        } elseif ($d < self::DESCRIPTION_MIN) {
            $out[] = ['part' => 'description', 'text' => 'The description is short (' . $d . ' characters).',
                      'tip'  => 'Add another sentence so it is ' . self::DESCRIPTION_MIN . '–' . self::DESCRIPTION_MAX . ' characters long.'];
        }

        return $out;
    }

    /** Two pages sharing a title or description compete with each other. */
    private function flagDuplicates(array &$rows): void
    {
        foreach (['title' => 'title', 'description' => 'description'] as $field => $word) {
            $tip = $field === 'title'
                ? 'Give each page its own title, so people can tell them apart in search results.'
                : 'Give each page its own description of what is on it.';
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
                    $rows[$i]['problems'][] = ['part' => $field, 'text' => 'Another page uses exactly the same ' . $word . '.', 'tip' => $tip];
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
