<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * The media library — an index of the optimised image set under
 * public/assets/img/machines/.
 *
 * Each row is one source image. `legacy_key` is the key the rest of the site
 * refers to it by (e.g. draw-bench/carriage), matching app/Data/ImageManifest.php;
 * `variants` holds the generated widths as {"1600":[w,h],"640":[w,h]}; `path`
 * points at the largest variant, relative to public/.
 *
 * The manifest file stays as the fallback. timb_manifest() merges these rows
 * over it, so an image uploaded here is usable immediately without
 * regenerating the file.
 */
class MediaModel extends Model
{
    protected $table         = 'media';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'legacy_key', 'original_name', 'path', 'mime', 'size',
        'width', 'height', 'alt_text', 'title', 'variants', 'uploaded_by',
    ];

    /** Widths generated for every image, matching tools/optimize_images.py. */
    public const SIZES = [1600, 640];

    /** Where the optimised set lives, relative to public/. */
    public const BASE = 'assets/img/machines/';

    /** WebP quality, matching tools/optimize_images.py. */
    public const WEBP_QUALITY = 78;

    protected $validationRules = [
        'id'         => 'permit_empty|is_natural_no_zero',
        'legacy_key' => 'required|max_length[255]|is_unique[media.legacy_key,id,{id}]',
        'title'      => 'permit_empty|max_length[500]',
        'alt_text'   => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'legacy_key' => ['is_unique' => 'An image with that name already exists in this folder.'],
    ];

    /** @var array<string,array>|null per-request cache */
    private static ?array $manifestCache = null;

    // ── Reading ───────────────────────────────────────────────────────────────

    public function allOrdered(): array
    {
        return $this->orderBy('legacy_key', 'ASC')->findAll();
    }

    public function findByKey(string $key): ?array
    {
        return $this->where('legacy_key', $key)->first();
    }

    /** Decoded variants for a row, as width => [w, h]. */
    public static function variants(array $row): array
    {
        $v = json_decode((string) ($row['variants'] ?? ''), true);

        return is_array($v) ? $v : [];
    }

    /**
     * The description (alt text) of an image, or '' when it has none.
     *
     * alt_text used to be filled with a copy of the title ("mill 1"), which
     * describes nothing, so a copy of the title counts as no description.
     */
    public static function description(array $row): string
    {
        $alt = trim((string) ($row['alt_text'] ?? ''));

        return $alt !== '' && strcasecmp($alt, trim((string) ($row['title'] ?? ''))) !== 0 ? $alt : '';
    }

    /** The folders in use, for the upload form. */
    public function folders(): array
    {
        $rows = $this->builder()->select('legacy_key')->get()->getResultArray();

        $folders = [];
        foreach ($rows as $r) {
            $slash = strpos((string) $r['legacy_key'], '/');
            if ($slash !== false) {
                $folders[] = substr((string) $r['legacy_key'], 0, $slash);
            }
        }

        $folders = array_values(array_unique($folders));
        sort($folders);

        return $folders;
    }

    /**
     * Rows shaped like app/Data/ImageManifest.php entries, keyed by legacy_key.
     * Used by timb_manifest() so uploads work without regenerating the file.
     *
     * @return array<string,array{title:string,alt:string,files:array}>
     */
    public static function manifestRows(): array
    {
        if (self::$manifestCache !== null) {
            return self::$manifestCache;
        }

        try {
            $rows = db_connect()->table('media')->select('legacy_key, title, alt_text, variants')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Media manifest unavailable, using the generated file: ' . $e->getMessage());

            return self::$manifestCache = [];
        }

        $out = [];
        foreach ($rows as $r) {
            $files = json_decode((string) $r['variants'], true);
            if (! is_array($files) || $files === []) {
                continue;
            }
            $out[(string) $r['legacy_key']] = [
                'title' => (string) ($r['title'] ?? ''),
                'alt'   => self::description($r),
                'files' => $files,
            ];
        }

        return self::$manifestCache = $out;
    }

    /** Forget the cached manifest after a write. */
    public static function forgetManifest(): void
    {
        self::$manifestCache = null;
    }

    // ── Usage ─────────────────────────────────────────────────────────────────

    /**
     * Where each image key is referenced, as key => ["Machines: Tube Mill", …].
     * Built in four queries rather than one per image.
     *
     * @return array<string,list<string>>
     */
    public function usage(): array
    {
        $db    = $this->db;
        $usage = [];

        $add = static function (?string $key, string $label) use (&$usage): void {
            $key = trim((string) $key);
            if ($key !== '') {
                $usage[$key][] = $label;
            }
        };

        try {
            foreach ($db->table('machines')->select('name, image_key')
                        ->where('deleted_at IS NULL', null, false)->get()->getResultArray() as $r) {
                $add($r['image_key'], 'Machine: ' . $r['name']);
            }

            foreach ($db->table('hero_slides')->select('name, image_key')->get()->getResultArray() as $r) {
                $add($r['image_key'], 'Hero slide: ' . $r['name']);
            }

            foreach ($db->table('news_articles')->select('title, hero_path')
                        ->where('deleted_at IS NULL', null, false)->get()->getResultArray() as $r) {
                $add($r['hero_path'], 'News hero: ' . $r['title']);
            }

            foreach ($db->table('news_gallery')->select('article_id, image_path')->get()->getResultArray() as $r) {
                $add($r['image_path'], 'News gallery');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Media usage lookup failed: ' . $e->getMessage());
        }

        return $usage;
    }

    // ── Files ─────────────────────────────────────────────────────────────────

    /**
     * Write the -1600 and -640 webp variants of a source image under a key,
     * matching tools/optimize_images.py: never upscale, keep the aspect ratio,
     * quality 78. Shared by uploads, replacements and the news-photo import.
     *
     * @return array<string,array{0:int,1:int}> width => [w, h]
     */
    public static function writeVariants(string $source, string $key): array
    {
        $info = @getimagesize($source);
        if ($info === false) {
            throw new \RuntimeException('the file is not a readable image');
        }
        [$srcW, $srcH] = $info;

        $dir = FCPATH . self::BASE . dirname($key);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException('the image folder could not be created');
        }

        $variants = [];
        $written  = [];

        try {
            foreach (self::SIZES as $maxW) {
                $w = $srcW;
                $h = $srcH;
                if ($w > $maxW) {
                    $h = (int) round($h * $maxW / $w);
                    $w = $maxW;
                }

                $target = FCPATH . self::BASE . $key . '-' . $maxW . '.webp';

                service('image')
                    ->withFile($source)
                    ->resize($w, $h, true)
                    ->convert(IMAGETYPE_WEBP)
                    ->save($target, self::WEBP_QUALITY);

                if (! is_file($target)) {
                    throw new \RuntimeException('a variant could not be written');
                }

                $written[]                = $target;
                $variants[(string) $maxW] = [$w, $h];
            }
        } catch (\Throwable $e) {
            foreach ($written as $file) {
                @unlink($file); // never leave half an image set behind
            }

            throw $e;
        }

        return $variants;
    }


    /** Absolute paths of every variant file for a key, those that exist. */
    public static function variantFiles(string $key): array
    {
        $files = [];
        foreach (self::SIZES as $size) {
            $file = FCPATH . self::BASE . $key . '-' . $size . '.webp';
            if (is_file($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /** Total bytes on disk for a key's variants. */
    public static function diskSize(string $key): int
    {
        $bytes = 0;
        foreach (self::variantFiles($key) as $file) {
            $bytes += (int) filesize($file);
        }

        return $bytes;
    }
}
