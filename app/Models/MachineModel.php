<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Machines — the products and solutions shown at /machines/<slug>.
 *
 * This model is the single source of truth for machine content. The public
 * site used to read Config\Site::$products / $solutions / $productMenu; those
 * arrays are now only the fallback used when the database is unreachable.
 *
 * Column notes:
 *   kind          product | solution — which listing the machine belongs to
 *   group_name    header menu group, e.g. "Forming Machines"
 *   menu_order    order in the product/solution listings (home, prev/next)
 *   nav_order     order inside the header menu; groups sort by their lowest
 *   image_key     key into app/Data/ImageManifest.php, e.g. tube-mill/mill-1
 *   visual_key    animated fallback illustration when there is no photo
 *   spec_sheet    basename of public/spec-sheets/<spec_sheet>.html
 *   details_json  the full product-page payload (specs, features, galleries)
 */
class MachineModel extends Model
{
    protected $table          = 'machines';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'slug', 'name', 'group_name', 'kind', 'series_label',
        'visual_key', 'image_key', 'spec_sheet', 'metric', 'summary',
        'menu_order', 'nav_order', 'show_in_menu', 'is_published', 'details_json',
    ];

    /** Illustration keys understood by app/Views/partials/visuals.php */
    public const VISUALS = ['cutter', 'robot', 'handling', 'gauge'];

    protected $validationRules = [
        // Carries the {id} placeholder in the slug rule below; CI requires the
        // placeholder field to have a rule of its own.
        'id'   => 'permit_empty|is_natural_no_zero',
        'slug' => 'required|max_length[255]|regex_match[/^[a-z0-9]+(?:-[a-z0-9]+)*$/]'
                . '|is_unique[machines.slug,id,{id}]',
        'name'         => 'required|max_length[255]',
        'group_name'   => 'required|max_length[50]',
        'kind'         => 'required|in_list[product,solution]',
        'series_label' => 'required|max_length[255]',
        'metric'       => 'required|max_length[255]',
        'summary'      => 'permit_empty|max_length[2000]',
        'image_key'    => 'permit_empty|max_length[255]',
        'spec_sheet'   => 'permit_empty|max_length[100]|regex_match[/^[a-z0-9-]*$/]',
        'visual_key'   => 'permit_empty|in_list[cutter,robot,handling,gauge]',
        'menu_order'   => 'permit_empty|is_natural',
        'nav_order'    => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'slug' => [
            'regex_match' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'is_unique'   => 'That slug is already in use. Note a deleted machine still holds its slug.',
        ],
        'visual_key' => ['in_list' => 'Choose one of the built-in illustrations, or leave blank.'],
        'kind'       => ['in_list' => 'Kind must be either product or solution.'],
    ];

    // ── Public-site queries ───────────────────────────────────────────────────

    /** @var array<string,array>|null per-request cache, dropped on every write */
    private ?array $publishedCache = null;

    protected $afterInsert = ['dropCache'];
    protected $afterUpdate = ['dropCache'];
    protected $afterDelete = ['dropCache'];

    protected function dropCache(array $data): array
    {
        $this->publishedCache = null;

        return $data;
    }

    /** All live machines, in listing order. */
    public function published(): array
    {
        return $this->publishedCache ??= $this->where('is_published', 1)
                                              ->orderBy('menu_order', 'ASC')->findAll();
    }

    /**
     * Live machines of one kind, shaped exactly like Config\Site::$products
     * entries so the existing views need no changes.
     */
    public function listing(string $kind): array
    {
        $out = [];
        foreach ($this->published() as $r) {
            if ($r['kind'] !== $kind) {
                continue;
            }
            $out[] = [
                'slug'    => $r['slug'],
                'name'    => $r['name'],
                'series'  => $r['series_label'],
                // img_tag() takes a string; a product with no photo must not fatal.
                'image'   => (string) ($r['image_key'] ?? ''),
                'spec'    => $r['spec_sheet'],
                'metric'  => $r['metric'],
                'summary' => (string) ($r['summary'] ?? ''),
                'visual'  => $r['visual_key'],
            ];
        }

        return $out;
    }

    /**
     * Header menu: group name => list of slugs. Groups are ordered by their
     * lowest nav_order, machines within a group by nav_order.
     */
    public function navMenu(): array
    {
        $rows = $this->where('is_published', 1)->where('show_in_menu', 1)
                     ->orderBy('nav_order', 'ASC')->findAll();

        $menu = [];
        foreach ($rows as $r) {
            $menu[$r['group_name']][] = $r['slug'];
        }

        return $menu;
    }

    /**
     * The full product-page payload for one slug, or null if the machine is
     * not live. Stored details_json is merged over a skeleton built from the
     * machine's own fields, so a machine added through the CMS renders a
     * usable page straight away instead of 404ing, and a partial payload
     * cannot leave the product view reaching for a missing key.
     */
    public function details(string $slug): ?array
    {
        $row = $this->where('slug', $slug)->where('is_published', 1)->first();
        if (! $row) {
            return null;
        }

        $stored = json_decode((string) ($row['details_json'] ?? ''), true);

        $details = is_array($stored)
            ? $stored + self::detailsSkeleton($row)
            : self::detailsSkeleton($row);

        $details['visual'] ??= $row['visual_key'];

        return $details;
    }

    /**
     * Every key app/Views/product.php reads, filled from the machine row.
     * Nothing here is invented copy: it is the machine's own name, series,
     * summary, metric and photo arranged into the shape the view expects.
     */
    public static function detailsSkeleton(array $row): array
    {
        $name    = (string) ($row['name'] ?? '');
        $summary = trim((string) ($row['summary'] ?? ''));

        return [
            'slug'        => $row['slug'] ?? '',
            'tag'         => $name,
            'title'       => ['', $name, ''],
            'tagline'     => $summary !== '' ? $summary : $name,
            'hero'        => ($row['image_key'] ?? '') ?: null,
            'heroCaption' => [trim(($row['series_label'] ?? '') . ' ' . $name), $summary],
            'specLabel'   => 'Key specifications',
            'specs'       => [['Key metric', (string) ($row['metric'] ?? '')]],
            'info'        => [],
            'callout'     => null,
            'features'    => null,
            'types'       => null,
            'mech'        => [],
            'apps'        => [],
            'galleries'   => [],
            'highlight'   => null,
            'visual'      => $row['visual_key'] ?? null,
            'cta'         => 'Ready to discuss your requirement?',
        ];
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    /** Every machine, including unpublished and soft-deleted, for the admin list. */
    public function forAdmin(): array
    {
        return $this->withDeleted()->orderBy('menu_order', 'ASC')->findAll();
    }

    /** Undo a soft delete. */
    public function restore(int $id): bool
    {
        return $this->builder()->where('id', $id)->update(['deleted_at' => null]);
    }

    /** Distinct group names already in use, for the admin form's suggestions. */
    public function groupNames(): array
    {
        $rows = $this->builder()
                     ->select('group_name, MIN(nav_order) AS first_nav')
                     ->where('deleted_at IS NULL', null, false)
                     ->groupBy('group_name')
                     ->orderBy('first_nav', 'ASC')
                     ->get()->getResultArray();

        return array_column($rows, 'group_name');
    }
}
