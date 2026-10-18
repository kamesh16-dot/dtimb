<?php

namespace App\Libraries;

/**
 * Page maths and links for any list on the site.
 *
 * It works from a total, so it serves both a list already in memory (call
 * slice()) and a database query (use offset and perPage as LIMIT/OFFSET).
 *
 * Links are query-only — "?page=3&status=new" — so they resolve against the
 * current address whatever the server does with index.php or a subfolder, and
 * every other query parameter (a filter, a tab) survives a page change.
 *
 * Two lists on one screen stay independent by giving each its own parameter
 * names, e.g. new Paginator(..., pageParam: 'dpage', perParam: 'dper').
 */
final class Paginator
{
    /** The choices offered in the "Show N per page" picker. */
    public const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    public readonly int $total;
    public readonly int $perPage;
    public readonly int $page;
    public readonly int $pages;
    public readonly int $offset;

    /**
     * @param array<string,mixed> $query the current query string, e.g. $_GET
     */
    public function __construct(
        int $total,
        array $query = [],
        int $defaultPerPage = 20,
        public readonly string $pageParam = 'page',
        public readonly string $perParam = 'per',
        private readonly bool $choosablePerPage = true,
    ) {
        $this->total = max(0, $total);

        $per = (int) ($query[$perParam] ?? 0);
        $this->perPage = $choosablePerPage && in_array($per, self::PER_PAGE_OPTIONS, true)
            ? $per
            : max(1, $defaultPerPage);

        $this->pages = max(1, (int) ceil($this->total / $this->perPage));

        // Past the end (say the last item on the last page was just deleted)
        // lands on the last page rather than an empty one.
        $page         = (int) ($query[$pageParam] ?? 1);
        $this->page   = min(max(1, $page), $this->pages);
        $this->offset = ($this->page - 1) * $this->perPage;

        unset($query[$pageParam]);
        $this->query = $query;
    }

    /** The query string minus this list's page number. */
    private array $query;

    /** Build from the current request. */
    public static function fromRequest(int $total, int $defaultPerPage = 20, string $pageParam = 'page', string $perParam = 'per', bool $choosablePerPage = true): self
    {
        $query = service('request')->getGet() ?? [];

        return new self($total, is_array($query) ? $query : [], $defaultPerPage, $pageParam, $perParam, $choosablePerPage);
    }

    /** The rows of the current page, from a list holding every row. */
    public function slice(array $rows): array
    {
        return array_slice(array_values($rows), $this->offset, $this->perPage);
    }

    /** 1-based position of the first row shown; 0 for an empty list. */
    public function from(): int
    {
        return $this->total === 0 ? 0 : $this->offset + 1;
    }

    /** 1-based position of the last row shown. */
    public function to(): int
    {
        return min($this->offset + $this->perPage, $this->total);
    }

    public function hasPages(): bool
    {
        return $this->pages > 1;
    }

    public function canChoosePerPage(): bool
    {
        return $this->choosablePerPage && $this->total > self::PER_PAGE_OPTIONS[0];
    }

    /**
     * The sizes to offer, always including the one in use, so a screen with
     * its own default (say 24 thumbnails) still shows it as selected.
     *
     * @return list<int>
     */
    public function perPageOptions(): array
    {
        $options = array_unique([...self::PER_PAGE_OPTIONS, $this->perPage]);
        sort($options);

        return $options;
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->pages;
    }

    /** Link to one page. Page 1 drops the parameter, so it matches the plain address. */
    public function url(int $page): string
    {
        $page = min(max(1, $page), $this->pages);

        return $this->link($page === 1 ? [] : [$this->pageParam => $page]);
    }

    /**
     * Link to the current page with extra parameters, e.g. with(['edit' => 4])
     * for an edit form that should open without losing the page.
     */
    public function with(array $params): string
    {
        $page = $this->page === 1 ? [] : [$this->pageParam => $this->page];

        return $this->link($params + $page);
    }

    /** Parameters the per-page form must carry so a change keeps filters and the other list's page. */
    public function hiddenFields(): array
    {
        $fields = $this->query;
        unset($fields[$this->perParam]);

        return array_filter($fields, static fn ($v) => is_scalar($v) && (string) $v !== '');
    }

    /**
     * Page numbers to show, with null standing for a gap:
     * 1 … 4 5 6 … 20. The first, last and $around either side of the
     * current page are always present.
     *
     * @return list<int|null>
     */
    public function window(int $around = 1): array
    {
        $show = [1, $this->pages];
        for ($p = $this->page - $around; $p <= $this->page + $around; $p++) {
            $show[] = $p;
        }

        $show = array_values(array_unique(array_filter($show, fn ($p) => $p >= 1 && $p <= $this->pages)));
        sort($show);

        $out  = [];
        $prev = 0;
        foreach ($show as $p) {
            if ($p - $prev === 2) {
                $out[] = $p - 1;      // a gap of one page: show it rather than "…"
            } elseif ($p - $prev > 2) {
                $out[] = null;
            }
            $out[] = $p;
            $prev  = $p;
        }

        return $out;
    }

    private function link(array $params): string
    {
        $query = array_filter($params + $this->query, static fn ($v) => is_scalar($v) && (string) $v !== '');

        return $query === [] ? '?' : '?' . http_build_query($query);
    }
}
