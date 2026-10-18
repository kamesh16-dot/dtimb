<?php

namespace App\Libraries;

use App\Models\SeoSettingModel;

/**
 * Where every image is used on the site.
 *
 * The media library used to look only at machine cards, hero slides and news,
 * so photos on product pages and those built into the page design showed as
 * "unused" and could be deleted from under the live site. This looks
 * everywhere an image key can be referenced:
 *
 *   machine cards          machines.image_key                         blocks delete
 *   product pages          machines.details_json, and the built-in    blocks delete
 *                          copies in app/Data/*ProductDetails.php
 *   home banner            hero_slides.image_key                      blocks delete
 *   news                   news_articles.hero_path, news_gallery      blocks delete
 *   link previews          the SEO "picture for shared links"          blocks delete
 *   page design            a key written into a view file             blocks delete
 *   home photo gallery     every photo outside product-* and news/    information only:
 *                                                                     deleting just drops it
 *                                                                     from the gallery
 *
 * A use that "blocks" means deleting the image would leave a broken picture.
 */
class MediaUsage
{
    /** @var array<string,list<array{label:string,link:?string,blocks:bool,movable:bool}>>|null */
    private ?array $uses = null;

    /**
     * @param list<string> $knownKeys every image key in the library
     */
    public function __construct(private array $knownKeys)
    {
    }

    /** Every use of every image, keyed by image key. */
    public function all(): array
    {
        if ($this->uses !== null) {
            return $this->uses;
        }

        $this->uses = [];
        $this->machines();
        $this->productFiles();
        $this->heroSlides();
        $this->news();
        $this->shareImage();
        $this->views();
        $this->homeGallery();

        return $this->uses;
    }

    /** @return list<array{label:string,link:?string,blocks:bool,movable:bool}> */
    public function of(string $key): array
    {
        return $this->all()[$key] ?? [];
    }

    /** Whether deleting the image would break something on the site. */
    public function blocksDelete(string $key): bool
    {
        foreach ($this->of($key) as $use) {
            if ($use['blocks']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether every place that uses the image can be updated automatically
     * when it moves folder. Product-page content and page design cannot.
     */
    public function canMove(string $key): bool
    {
        foreach ($this->of($key) as $use) {
            if (! $use['movable']) {
                return false;
            }
        }

        return true;
    }

    // ── Sources ──────────────────────────────────────────────────────────────

    private function add(string $key, string $label, ?string $link, bool $blocks = true, bool $movable = true): void
    {
        $key = trim($key);
        if ($key === '') {
            return;
        }

        foreach ($this->uses[$key] ?? [] as $use) {
            if ($use['label'] === $label) {
                return; // already listed, e.g. a product page in both the DB and the file
            }
        }

        $this->uses[$key][] = ['label' => $label, 'link' => $link, 'blocks' => $blocks, 'movable' => $movable];
    }

    private function machines(): void
    {
        $this->safely('machines', function () {
            $rows = db_connect()->table('machines')->select('id, name, image_key, details_json')
                ->where('deleted_at IS NULL', null, false)->get()->getResultArray();

            foreach ($rows as $r) {
                $edit = '/admin/machines/edit/' . $r['id'];
                $this->add((string) $r['image_key'], 'Machine card: ' . $r['name'], $edit);

                $details = json_decode((string) ($r['details_json'] ?? ''), true);
                foreach (self::keysIn($details, $this->knownKeys) as $key) {
                    // Product-page content cannot be edited in the CMS, so these
                    // references cannot be rewritten if the image moves.
                    $this->add($key, 'Product page: ' . $r['name'], $edit, true, false);
                }
            }
        });
    }

    /** The built-in copies used when the database cannot be read. */
    private function productFiles(): void
    {
        $this->safely('product files', function () {
            $files = (require APPPATH . 'Data/ProductDetails.php') + (require APPPATH . 'Data/ExtraProductDetails.php');
            $names = array_column(array_merge(config('Site')->products, config('Site')->solutions), 'name', 'slug');

            foreach ($files as $slug => $details) {
                foreach (self::keysIn($details, $this->knownKeys) as $key) {
                    $this->add($key, 'Product page: ' . ($names[$slug] ?? $slug), null, true, false);
                }
            }
        });
    }

    private function heroSlides(): void
    {
        $this->safely('hero slides', function () {
            foreach (db_connect()->table('hero_slides')->select('id, name, image_key')->get()->getResultArray() as $r) {
                $this->add((string) $r['image_key'], 'Home banner: ' . $r['name'], '/admin/lists/hero_slides/edit/' . $r['id']);
            }
        });
    }

    private function news(): void
    {
        $this->safely('news', function () {
            $db       = db_connect();
            $articles = $db->table('news_articles')->select('id, title, hero_path')
                ->where('deleted_at IS NULL', null, false)->get()->getResultArray();
            $titles   = array_column($articles, 'title', 'id');

            foreach ($articles as $a) {
                $this->add((string) $a['hero_path'], 'News: ' . $a['title'], '/admin/news/edit/' . $a['id']);
            }
            foreach ($db->table('news_gallery')->select('article_id, image_path')->get()->getResultArray() as $g) {
                if (isset($titles[$g['article_id']])) {
                    $this->add((string) $g['image_path'], 'News: ' . $titles[$g['article_id']], '/admin/news/edit/' . $g['article_id']);
                }
            }
        });
    }

    private function shareImage(): void
    {
        $this->safely('share image', function () {
            $this->add(SeoSettingModel::get('share_image'), 'Picture for shared links (SEO)', '/admin/seo?tab=settings');
        });
    }

    /** Keys written straight into a template, e.g. img_tag('pilger-mill/pilger-3', …). */
    private function views(): void
    {
        $this->safely('views', function () {
            $known = array_flip($this->knownKeys);
            $files = array_merge(glob(APPPATH . 'Views/*.php') ?: [], glob(APPPATH . 'Views/pages/*.php') ?: [], glob(APPPATH . 'Views/partials/*.php') ?: []);

            foreach ($files as $file) {
                preg_match_all("#['\"]([a-z0-9-]+/[a-z0-9-]+)['\"]#", (string) file_get_contents($file), $m);
                foreach (array_unique($m[1]) as $key) {
                    if (isset($known[$key])) {
                        $page = basename($file, '.php');
                        $this->add($key, 'Built into the ' . ($page === 'home' ? 'home page' : $page . ' page') . ' design', null, true, false);
                    }
                }
            }
        });
    }

    /** App\Controllers\Home shows every photo outside product-* and news/. */
    private function homeGallery(): void
    {
        foreach ($this->knownKeys as $key) {
            if (self::inHomeGallery($key)) {
                $this->add($key, 'Home page photo gallery', null, false, true);
            }
        }
    }

    public static function inHomeGallery(string $key): bool
    {
        $folder = strstr($key, '/', true) ?: '';

        return ! str_starts_with($folder, 'product-') && $folder !== 'news';
    }

    private function safely(string $what, callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            log_message('error', "Media usage: could not check {$what}: " . $e->getMessage());
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Every known image key appearing anywhere in a nested structure.
     *
     * @return list<string>
     */
    public static function keysIn(mixed $data, array $knownKeys): array
    {
        if (! is_array($data)) {
            return [];
        }

        $known = array_flip($knownKeys);
        $found = [];
        array_walk_recursive($data, static function ($v) use ($known, &$found) {
            if (is_string($v) && isset($known[$v])) {
                $found[$v] = true;
            }
        });

        return array_keys($found);
    }
}
