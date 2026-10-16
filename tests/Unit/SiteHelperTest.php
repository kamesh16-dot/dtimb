<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for app/Helpers/site_helper.php.
 *
 * Tests: img_url(), img_tag(), asset_url(), india_xy(), timb_manifest().
 */
class SiteHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'site']);
    }

    // ── img_url ───────────────────────────────────────────────────────────────

    public function testImgUrlReturnsWebpPath(): void
    {
        $url = img_url('tube-mill/mill-1', '640');
        $this->assertStringEndsWith('-640.webp', $url);
        $this->assertStringContainsString('tube-mill/mill-1', $url);
    }

    public function testImgUrlDefaultSizeIs640(): void
    {
        $url640  = img_url('tube-mill/mill-1', '640');
        $urlDef  = img_url('tube-mill/mill-1');
        $this->assertSame($url640, $urlDef);
    }

    public function testImgUrl1600Variant(): void
    {
        $url = img_url('tube-mill/mill-1', '1600');
        $this->assertStringEndsWith('-1600.webp', $url);
    }

    // ── india_xy ──────────────────────────────────────────────────────────────

    public function testIndiaXyWorks(): void
    {
        // Works lat/lon = Poochiathipedu factory: lat 13.17, lon 80.02
        [$x, $y] = india_xy(13.17, 80.02);
        $this->assertIsFloat($x);
        $this->assertIsFloat($y);
        $this->assertGreaterThan(0.0,   $x);
        $this->assertLessThan(100.0, $x);
        $this->assertGreaterThan(0.0,   $y);
        $this->assertLessThan(100.0, $y);
    }

    public function testIndiaXyFarWestIsLowX(): void
    {
        // lon 68 = left edge → x ≈ 0
        [$x] = india_xy(20.0, 68.0);
        $this->assertEqualsWithDelta(0.0, $x, 1.0);
    }

    public function testIndiaXyFarEastIsHighX(): void
    {
        // lon 97.6 = right edge → x ≈ 100
        [$x] = india_xy(20.0, 97.6);
        $this->assertEqualsWithDelta(100.0, $x, 1.0);
    }

    // ── timb_manifest ─────────────────────────────────────────────────────────

    public function testTimbManifestReturnsArray(): void
    {
        $m = timb_manifest();
        $this->assertIsArray($m);
        $this->assertNotEmpty($m);
    }

    public function testTimbManifestKeysHaveFilesEntry(): void
    {
        $m = timb_manifest();
        foreach (array_slice($m, 0, 5) as $key => $entry) {
            $this->assertArrayHasKey('files', $entry, "Key '{$key}' missing 'files'");
            $this->assertArrayHasKey('640',   $entry['files'], "Key '{$key}' missing '640' size");
        }
    }

    public function testTimbManifestIsCached(): void
    {
        // Calling twice should return the same (identical) array instance
        $a = timb_manifest();
        $b = timb_manifest();
        $this->assertSame($a, $b);
    }

    // ── asset_url ─────────────────────────────────────────────────────────────

    public function testAssetUrlContainsPath(): void
    {
        $url = asset_url('assets/css/style.css');
        $this->assertStringContainsString('assets/css/style.css', $url);
    }
}
