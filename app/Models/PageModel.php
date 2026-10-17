<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Site pages — one row per public page, keyed by `key` (home, about, …).
 *
 * A page row owns three things:
 *   - the browser title and meta description (seo_title, seo_description)
 *   - the opening paragraph (intro)
 *   - structured content blocks for pages that have them (body_blocks)
 *
 * Only About and Infrastructure carry blocks; the other pages draw their
 * content from the list tables instead. App\Data\Content stays as the
 * fallback used when the database is unreachable.
 */
class PageModel extends Model
{
    protected $table          = 'pages';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'key', 'title', 'intro', 'body_blocks',
        'seo_title', 'seo_description', 'og_media_id', 'is_published',
    ];

    protected $validationRules = [
        'id'  => 'permit_empty|is_natural_no_zero',
        'key' => 'required|max_length[255]|regex_match[/^[a-z0-9]+(?:-[a-z0-9]+)*$/]'
               . '|is_unique[pages.key,id,{id}]',
        'title'           => 'required|max_length[255]',
        'seo_title'       => 'required|max_length[255]',
        'seo_description' => 'permit_empty|max_length[320]',
        'og_media_id'     => 'permit_empty|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'key' => [
            'regex_match' => 'The key may only contain lowercase letters, numbers and single hyphens.',
            'is_unique'   => 'That key is already in use.',
        ],
        'seo_description' => [
            'max_length' => 'Search engines cut the description off around 160 characters; keep it under 320.',
        ],
        'seo_title' => ['required' => 'A browser title is required — it is what search results show.'],
    ];

    /** @var array<string,array>|null per-request cache, dropped on every write */
    private ?array $byKeyCache = null;

    protected $afterInsert = ['dropCache'];
    protected $afterUpdate = ['dropCache'];
    protected $afterDelete = ['dropCache'];

    protected function dropCache(array $data): array
    {
        $this->byKeyCache = null;

        return $data;
    }

    // ── Public-site queries ───────────────────────────────────────────────────

    /** Live pages keyed by their `key`. One query per request. */
    public function byKey(): array
    {
        if ($this->byKeyCache !== null) {
            return $this->byKeyCache;
        }

        $rows = $this->where('is_published', 1)->findAll();

        return $this->byKeyCache = array_column($rows, null, 'key');
    }

    /**
     * Browser title and meta description for one page.
     *
     * @return array{0:?string,1:?string} [seo_title, seo_description]; either
     *                                    may be null, meaning "keep the default"
     */
    public function seo(string $key): array
    {
        $row = $this->byKey()[$key] ?? null;
        if ($row === null) {
            return [null, null];
        }

        return [
            ($row['seo_title'] ?? '') !== '' ? $row['seo_title'] : null,
            ($row['seo_description'] ?? '') !== '' ? $row['seo_description'] : null,
        ];
    }

    /**
     * Content blocks for one page, with the intro put back as `lead` so the
     * page views receive the same shape they always have. Returns null when
     * the page has no stored blocks, so the caller keeps its file defaults.
     */
    public function blocks(string $key): ?array
    {
        $row = $this->byKey()[$key] ?? null;
        if ($row === null) {
            return null;
        }

        $blocks = json_decode((string) ($row['body_blocks'] ?? ''), true);
        $blocks = is_array($blocks) ? $blocks : [];

        $intro = trim((string) ($row['intro'] ?? ''));
        if ($intro !== '') {
            $blocks = ['lead' => $intro] + $blocks;
        }

        return $blocks === [] ? null : $blocks;
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    /** Every page, in the order the site presents them. */
    public function forAdmin(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    public function findByKey(string $key): ?array
    {
        return $this->where('key', $key)->first();
    }

    /** Names of the blocks a page holds, for display in the admin form. */
    public function blockNames(string $key): array
    {
        $row = $this->findByKey($key);
        if ($row === null) {
            return [];
        }

        $blocks = json_decode((string) ($row['body_blocks'] ?? ''), true);

        return is_array($blocks) ? array_keys($blocks) : [];
    }

    // ── Block payload helpers ─────────────────────────────────────────────────

    /** Pretty-printed blocks for the admin textarea. */
    public static function blocksToText(?string $json): string
    {
        $raw = trim((string) $json);
        if ($raw === '') {
            return '';
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $raw;
        }

        return (string) json_encode(
            $decoded,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Admin textarea back into stored JSON.
     *
     * @return array{0:bool,1:string} [valid, json or the error message]
     */
    public static function blocksFromText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [true, ''];
        }

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [false, 'Content blocks are not valid JSON: ' . json_last_error_msg() . '.'];
        }
        if (! is_array($decoded)) {
            return [false, 'Content blocks must be a JSON object.'];
        }

        // lead lives in the intro field; keeping a copy here would let the two
        // disagree, and blocks() would silently prefer intro.
        unset($decoded['lead']);

        return [true, (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
    }
}
