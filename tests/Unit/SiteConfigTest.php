<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Site;

/**
 * Unit tests for Config\Site — verifies that every product and solution
 * listed in the config is self-consistent, and that helper functions
 * built on top of it produce correct results.
 */
class SiteConfigTest extends CIUnitTestCase
{
    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'site']);
        $this->site = config('Site');
    }

    // ── Products ──────────────────────────────────────────────────────────────

    public function testProductsIsNonEmptyArray(): void
    {
        $this->assertIsArray($this->site->products);
        $this->assertNotEmpty($this->site->products);
    }

    public function testEveryProductHasRequiredKeys(): void
    {
        $required = ['slug', 'name', 'series', 'image', 'spec', 'metric', 'summary'];
        foreach ($this->site->products as $p) {
            foreach ($required as $key) {
                $this->assertArrayHasKey($key, $p, "Product '{$p['name']}' missing key '{$key}'");
            }
        }
    }

    public function testProductSlugsAreUnique(): void
    {
        $slugs = array_column($this->site->products, 'slug');
        $this->assertSame(count($slugs), count(array_unique($slugs)), 'Duplicate product slug found');
    }

    public function testProductSlugMatchesMachineImageInManifest(): void
    {
        $manifest = timb_manifest();
        foreach ($this->site->products as $p) {
            if ($p['image'] !== null) {
                $this->assertArrayHasKey(
                    $p['image'],
                    $manifest,
                    "Product '{$p['slug']}' image key '{$p['image']}' not found in ImageManifest"
                );
            }
        }
    }

    // ── Solutions ─────────────────────────────────────────────────────────────

    public function testSolutionsIsNonEmptyArray(): void
    {
        $this->assertIsArray($this->site->solutions);
        $this->assertNotEmpty($this->site->solutions);
    }

    public function testSolutionSlugsAreUnique(): void
    {
        $slugs = array_column($this->site->solutions, 'slug');
        $this->assertSame(count($slugs), count(array_unique($slugs)), 'Duplicate solution slug found');
    }

    public function testNoSlugOverlapBetweenProductsAndSolutions(): void
    {
        $pSlugs = array_column($this->site->products, 'slug');
        $sSlugs = array_column($this->site->solutions, 'slug');
        $overlap = array_intersect($pSlugs, $sSlugs);
        $this->assertEmpty($overlap, 'Slug(s) appear in both products and solutions: ' . implode(', ', $overlap));
    }

    // ── Product menu ──────────────────────────────────────────────────────────

    public function testProductMenuOnlyReferencesKnownSlugs(): void
    {
        $all = array_column(array_merge($this->site->products, $this->site->solutions), 'slug');
        foreach ($this->site->productMenu as $group => $slugs) {
            foreach ($slugs as $slug) {
                $this->assertContains($slug, $all, "productMenu group '{$group}' references unknown slug '{$slug}'");
            }
        }
    }

    // ── Hero slides ───────────────────────────────────────────────────────────

    public function testHeroSlidesEachHaveThreeTitleParts(): void
    {
        foreach ($this->site->heroSlides as $i => $slide) {
            $this->assertCount(3, $slide['title'], "heroSlides[{$i}] must have exactly 3 title parts");
        }
    }

    public function testHeroSlideImageKeysExistInManifest(): void
    {
        $manifest = timb_manifest();
        foreach ($this->site->heroSlides as $i => $slide) {
            $this->assertArrayHasKey(
                $slide['image'],
                $manifest,
                "heroSlides[{$i}] image key '{$slide['image']}' not in manifest"
            );
        }
    }

    // ── Network / locations ───────────────────────────────────────────────────

    public function testNetworkLocationsHaveRequiredKeys(): void
    {
        $required = ['name', 'type', 'place', 'address', 'phones', 'lat', 'lon', 'callout'];
        foreach ($this->site->network as $i => $loc) {
            foreach ($required as $key) {
                $this->assertArrayHasKey($key, $loc, "network[{$i}] missing key '{$key}'");
            }
            $this->assertCount(2, $loc['callout'], "network[{$i}]['callout'] must have [x, y]");
            $this->assertNotEmpty($loc['phones'], "network[{$i}] must have at least one phone");
        }
    }

    public function testNetworkLatLonAreWithinIndiaBounds(): void
    {
        foreach ($this->site->network as $i => $loc) {
            $this->assertGreaterThanOrEqual(6.5,  $loc['lat'], "network[{$i}] lat below India south bound");
            $this->assertLessThanOrEqual(37.3,    $loc['lat'], "network[{$i}] lat above India north bound");
            $this->assertGreaterThanOrEqual(68.0, $loc['lon'], "network[{$i}] lon west of India");
            $this->assertLessThanOrEqual(97.6,    $loc['lon'], "network[{$i}] lon east of India");
        }
    }
}
