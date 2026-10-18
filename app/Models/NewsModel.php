<?php

namespace App\Models;

use App\Libraries\CmsCache;
use CodeIgniter\Model;

/**
 * News articles shown on /news.
 *
 * This model is the source of truth for news content. App\Data\Content's
 * "news" array is now only the fallback used when the database is unreachable.
 *
 * Column notes:
 *   published_on  the article date, shown on the page and used for ordering
 *   tag           small label above the headline, e.g. "Events"
 *   summary       the teaser shown before the article is expanded
 *   hero_path     image under public/assets/img/, without the .webp suffix
 *   intro         opening paragraph, styled as a lead
 *   quote         pull quote
 *   body          JSON array of paragraphs; see bodyToArray()/bodyToJson()
 *   sort_order    tie-breaker for articles sharing a date
 *
 * Gallery images live in the news_gallery table, keyed by article_id.
 */
class NewsModel extends Model
{
    protected $table          = 'news_articles';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'slug', 'title', 'published_on', 'tag', 'summary',
        'hero_path', 'hero_alt', 'intro', 'quote', 'body',
        'is_published', 'sort_order',
    ];

    protected $validationRules = [
        // Carries the {id} placeholder in the slug rule; CI requires the
        // placeholder field to have a rule of its own.
        'id'   => 'permit_empty|is_natural_no_zero',
        'slug' => 'required|max_length[255]|regex_match[/^[a-z0-9]+(?:-[a-z0-9]+)*$/]'
                . '|is_unique[news_articles.slug,id,{id}]',
        'title'        => 'required|max_length[255]',
        'published_on' => 'required|valid_date[Y-m-d]',
        'tag'          => 'permit_empty|max_length[255]',
        'summary'      => 'permit_empty|max_length[2000]',
        'hero_path'    => 'permit_empty|max_length[255]',
        'hero_alt'     => 'permit_empty|max_length[2000]',
        'sort_order'   => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'slug' => [
            'regex_match' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'is_unique'   => 'That slug is already in use. Note a deleted article still holds its slug.',
        ],
        'published_on' => ['valid_date' => 'Enter the date as YYYY-MM-DD.'],
        'hero_alt'     => ['max_length' => 'Keep the hero description under 2000 characters.'],
    ];

    /** @var array<int,array>|null per-request cache, dropped on every write */
    private ?array $publishedCache = null;

    protected $afterInsert = ['dropCache'];
    protected $afterUpdate = ['dropCache'];
    protected $afterDelete = ['dropCache'];

    protected function dropCache(array $data): array
    {
        $this->publishedCache = null;

        return $data;
    }

    // ── Public-site queries ───────────────────────────────────────────────────

    /** Live articles, newest first. */
    public function published(): array
    {
        return $this->publishedCache ??= CmsCache::remember('news_published', fn () => $this->where('is_published', 1)
                                              ->orderBy('published_on', 'DESC')
                                              ->orderBy('sort_order', 'ASC')
                                              ->findAll());
    }

    /**
     * Live articles shaped exactly like App\Data\Content's "news" entries, so
     * app/Views/pages/news.php needs no changes.
     */
    public function listing(): array
    {
        $rows = $this->published();
        if ($rows === []) {
            return [];
        }

        $galleries = $this->galleriesFor(array_column($rows, 'id'));

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'title'   => (string) $r['title'],
                'date'    => (string) $r['published_on'],
                'tag'     => (string) ($r['tag'] ?? ''),
                'summary' => (string) ($r['summary'] ?? ''),
                // The view does `if (! empty($a['hero']))` then reads [0] and [1].
                'hero'    => $r['hero_path']
                    ? [(string) $r['hero_path'], (string) ($r['hero_alt'] ?? '')]
                    : null,
                'intro'   => (string) ($r['intro'] ?? ''),
                'quote'   => (string) ($r['quote'] ?? ''),
                'gallery' => $galleries[$r['id']] ?? [],
                'body'    => self::bodyToArray($r['body'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Gallery rows for the given article ids, as [article_id => [[path, alt], …]].
     * One query for all articles rather than one per article.
     */
    public function galleriesFor(array $articleIds): array
    {
        if ($articleIds === []) {
            return [];
        }

        $rows = CmsCache::remember('news_gallery_' . md5(implode(',', $articleIds)), fn () => $this->db->table('news_gallery')
                         ->whereIn('article_id', $articleIds)
                         ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')
                         ->get()->getResultArray());

        $out = [];
        foreach ($rows as $g) {
            $out[(int) $g['article_id']][] = [(string) $g['image_path'], (string) ($g['alt'] ?? '')];
        }

        return $out;
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    /** Every article, including unpublished and soft-deleted. */
    public function forAdmin(): array
    {
        return $this->withDeleted()
                    ->orderBy('published_on', 'DESC')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /** Undo a soft delete. */
    public function restore(int $id): bool
    {
        return $this->builder()->where('id', $id)->update(['deleted_at' => null]);
    }

    /** Gallery rows for one article, in order, as full records for editing. */
    public function gallery(int $articleId): array
    {
        return $this->db->table('news_gallery')
                        ->where('article_id', $articleId)
                        ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')
                        ->get()->getResultArray();
    }

    /**
     * Replace an article's gallery with the given [path, alt] pairs. Rows are
     * rewritten rather than diffed: the set is small and order matters.
     */
    public function saveGallery(int $articleId, array $images): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();
        $this->db->table('news_gallery')->where('article_id', $articleId)->delete();

        $i = 0;
        foreach ($images as [$path, $alt]) {
            $path = trim((string) $path);
            if ($path === '') {
                continue;
            }
            $this->db->table('news_gallery')->insert([
                'article_id' => $articleId,
                'image_path' => $path,
                'alt'        => trim((string) $alt),
                'sort_order' => $i++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $this->db->transComplete();
    }

    // ── Body conversion ───────────────────────────────────────────────────────

    /**
     * The body column holds a JSON array of paragraphs. Legacy or hand-edited
     * rows may hold plain text; treat those as a single paragraph so nothing
     * is lost.
     *
     * @return list<string>
     */
    public static function bodyToArray(?string $json): array
    {
        $raw = trim((string) $json);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $paras = array_map(static fn ($p) => trim((string) $p), $decoded);

            return array_values(array_filter($paras, static fn ($p) => $p !== ''));
        }

        return [$raw];
    }

    /** Blank-line-separated text from the admin form back into JSON paragraphs. */
    public static function bodyToJson(string $text): string
    {
        $text  = str_replace(["\r\n", "\r"], "\n", $text);
        $paras = preg_split('/\n\s*\n/', trim($text)) ?: [];
        $paras = array_values(array_filter(array_map('trim', $paras), static fn ($p) => $p !== ''));

        return json_encode($paras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** JSON paragraphs back into blank-line-separated text for the form. */
    public static function bodyToText(?string $json): string
    {
        return implode("\n\n", self::bodyToArray($json));
    }
}
