<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\PageModel;

/** Company pages: /about /infrastructure /projects /clients /industries /careers. Copy: App\Data\Content. */
class Pages extends BaseController
{
    private ?PageModel $pages = null;

    private function page(string $view, string $title, string $desc, array $extra = []): string
    {
        $site = config('Site');

        // The view name doubles as the page key in the pages table.
        $content = $extra['c'] ?? require APPPATH . 'Data/Content.php';
        unset($extra['c']);

        // Lists Hub content wins over the file copy, list by list.
        try {
            $content = $this->siteLists()->applyToContent($content);
        } catch (\Throwable $e) {
            log_message('error', 'List overlay failed, using Content defaults: ' . $e->getMessage());
        }

        // Merge rather than replace: whatever the CMS holds wins, and anything
        // it has no row for falls back to App\Data\Content, so a page view can
        // never reach for a block that is simply not stored yet.
        $blocks = $this->pageBlocks($view);
        if ($blocks !== null) {
            $content[$view] = $blocks + (array) ($content[$view] ?? []);
        }

        [$seoTitle, $seoDesc] = $this->pageSeo($view);

        return view('pages/' . $view, $extra + [
            'site'     => $site,
            'c'        => $content,
            'all'      => array_merge($site->products, $site->solutions),
            'title'    => $seoTitle ?? ($title . ' | ' . $site->name),
            'metaDesc' => $seoDesc ?? $desc,
            'preload'  => null,
        ]);
    }

    /**
     * Browser title and meta description from the CMS. Either may be null,
     * meaning the caller's own default stands.
     *
     * @return array{0:?string,1:?string}
     */
    private function pageSeo(string $key): array
    {
        try {
            return $this->pageModel()->seo($key);
        } catch (\Throwable $e) {
            log_message('error', 'Page SEO lookup failed: ' . $e->getMessage());

            return [null, null];
        }
    }

    /** Content blocks from the CMS, or null to keep App\Data\Content's copy. */
    private function pageBlocks(string $key): ?array
    {
        try {
            return $this->pageModel()->blocks($key);
        } catch (\Throwable $e) {
            log_message('error', 'Page blocks lookup failed: ' . $e->getMessage());

            return null;
        }
    }

    private function pageModel(): PageModel
    {
        return $this->pages ??= new PageModel();
    }

    public function about(): string
    {
        return $this->page('about', 'About', 'TI Machine Building: a unit of Tube Investments of India and the Murugappa Group, building custom machines, automation and tooling since 2012.');
    }

    public function infrastructure(): string
    {
        return $this->page('infrastructure', 'Infrastructure', 'Design tools, assembly, inspection and laboratory facilities behind every TIMB machine.');
    }

    public function projects(): string
    {
        return $this->page('projects', 'Projects', 'Completed projects from TIMB: tube mills, straightening, swaging, test rigs, vision inspection and line automation.');
    }

    public function clients(): string
    {
        return $this->page('clients', 'Clients & FAQ', 'Who TIMB builds for, and answers to common questions about quotations, documents and services.');
    }

    public function industries(): string
    {
        return $this->page('industries', 'Industries', 'Automotive, railways, tube processing, EV, and heavy engineering: where TIMB machines work.');
    }

    public function careers(): string
    {
        return $this->page('careers', 'Careers', 'Work on custom machines, automation and tooling at TI Machine Building.');
    }

    public function news(): string
    {
        $content = require APPPATH . 'Data/Content.php';
        $content['news'] = $this->newsArticles();

        return $this->page('news', 'Latest News', 'News from TI Machine Building.', ['c' => $content]);
    }

    /**
     * Articles from the CMS, falling back to App\Data\Content when the
     * database is unreachable or holds nothing published.
     *
     * @return list<array<string,mixed>>
     */
    private function newsArticles(): array
    {
        try {
            $articles = (new NewsModel())->listing();
            if ($articles !== []) {
                return $articles;
            }
        } catch (\Throwable $e) {
            log_message('error', 'News lookup failed, using Content defaults: ' . $e->getMessage());
        }

        return (require APPPATH . 'Data/Content.php')['news'] ?? [];
    }

    public function disclaimer(): string
    {
        return $this->page('disclaimer', 'Disclaimer', 'Website disclaimer for TI Machine Building.');
    }

    public function contact(): string
    {
        return $this->page('contact', 'Contact Us', 'Contact TI Machine Building: works, corporate office, phone numbers and an enquiry form.', [
            'machine' => (string) $this->request->getGet('machine'),
        ]);
    }
}
