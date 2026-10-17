<?php

namespace App\Libraries;

use App\Models\ListItemModel;

/**
 * Turns the Lists Hub tables into the exact arrays the public views already
 * read from Config\Site and App\Data\Content.
 *
 * Every method returns null when the list is empty or unreadable, which means
 * "keep the hard-coded copy" — the site never renders a blank section because
 * a table could not be read.
 */
class SiteLists
{
    /** @var array<string,array|null> */
    private array $cache = [];

    /** Active rows of one list, already flattened to field values. */
    private function rows(string $key): ?array
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        try {
            $list = ListItemModel::make($key);
            if ($list === null) {
                return $this->cache[$key] = null;
            }

            $out = [];
            foreach ($list->active() as $row) {
                $out[] = $list->values($row);
            }

            return $this->cache[$key] = $out === [] ? null : $out;
        } catch (\Throwable $e) {
            log_message('error', "List '{$key}' unavailable, using defaults: " . $e->getMessage());

            return $this->cache[$key] = null;
        }
    }

    /** Positional lists: [[value, caption], …] */
    private function tuples(string $key, array $fields): ?array
    {
        $rows = $this->rows($key);
        if ($rows === null) {
            return null;
        }

        return array_map(
            static fn ($r) => array_map(static fn ($f) => $r[$f] ?? '', $fields),
            $rows
        );
    }

    // ── Config\Site replacements ─────────────────────────────────────────────

    public function stats(): ?array
    {
        return $this->tuples('stats', ['value', 'caption']);
    }

    public function pillars(): ?array
    {
        return $this->tuples('pillars', ['number', 'title', 'text']);
    }

    public function process(): ?array
    {
        return $this->tuples('process_steps', ['number', 'title', 'machines']);
    }

    public function lineStages(): ?array
    {
        return $this->tuples('line_stages', ['number', 'title', 'metric', 'metric_label', 'machines']);
    }

    public function homeIndustries(): ?array
    {
        $rows = $this->rows('home_industries');

        return $rows === null ? null : array_column($rows, 'name');
    }

    public function heroSlides(): ?array
    {
        $rows = $this->rows('hero_slides');
        if ($rows === null) {
            return null;
        }

        return array_map(static fn ($r) => [
            'image'  => $r['image_key'],
            'label'  => $r['label'],
            'name'   => $r['name'],
            'metric' => $r['metric'],
            'spec'   => $r['spec'],
            'title'  => $r['title_json'],
            'copy'   => $r['copy'],
        ], $rows);
    }

    public function network(): ?array
    {
        $rows = $this->rows('network_nodes');
        if ($rows === null) {
            return null;
        }

        return array_map(static fn ($r) => [
            'name'    => $r['name'],
            'type'    => $r['type'],
            'place'   => $r['place'],
            'sub'     => $r['sub'],
            'address' => $r['address'],
            'phones'  => $r['phones_json'],
            'lat'     => (float) $r['lat'],
            'lon'     => (float) $r['lon'],
            'callout' => $r['callout_json'],
        ], $rows);
    }

    // ── App\Data\Content replacements ────────────────────────────────────────

    public function projects(): ?array
    {
        return $this->tuples('projects', ['name', 'machine_slug']);
    }

    public function cases(): ?array
    {
        return $this->tuples('cases', ['title', 'tag', 'description']);
    }

    public function faq(): ?array
    {
        return $this->tuples('faq_items', ['question', 'answer']);
    }

    public function careerAreas(): ?array
    {
        $rows = $this->rows('career_areas');

        return $rows === null ? null : array_column($rows, 'name');
    }

    public function disclaimer(): ?array
    {
        $rows = $this->rows('disclaimer_points');

        return $rows === null ? null : array_column($rows, 'body');
    }

    /** ['delivered' => [[name, work], …], 'names' => [name, …]] */
    public function clients(): ?array
    {
        $delivered = $this->tuples('clients', ['name', 'delivered_work']);
        $names     = $this->rows('client_names');

        if ($delivered === null && $names === null) {
            return null;
        }

        return [
            'delivered' => $delivered ?? [],
            'names'     => $names === null ? [] : array_column($names, 'name'),
        ];
    }

    public function industries(): ?array
    {
        return $this->tuples('industries', ['slug', 'name', 'description', 'items_json', 'machine_slugs_json']);
    }

    /**
     * Overlay every available list onto a copy of App\Data\Content.
     * Keys with no stored rows keep the file's version.
     */
    public function applyToContent(array $content): array
    {
        foreach ([
            'projects'    => $this->projects(),
            'cases'       => $this->cases(),
            'faq'         => $this->faq(),
            'careerAreas' => $this->careerAreas(),
            'disclaimer'  => $this->disclaimer(),
            'clients'     => $this->clients(),
            'industries'  => $this->industries(),
        ] as $key => $value) {
            if ($value !== null) {
                $content[$key] = $value;
            }
        }

        return $content;
    }

    /** Overlay every available list onto the shared Config\Site instance. */
    public function applyToSite(object $site): void
    {
        foreach ([
            'stats'      => $this->stats(),
            'pillars'    => $this->pillars(),
            'process'    => $this->process(),
            'lineStages' => $this->lineStages(),
            'industries' => $this->homeIndustries(),
            'heroSlides' => $this->heroSlides(),
            'network'    => $this->network(),
        ] as $property => $value) {
            if ($value !== null) {
                $site->{$property} = $value;
            }
        }
    }
}
