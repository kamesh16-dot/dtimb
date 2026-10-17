<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Smoke-tests every public route.
 *
 * CI4 4.5 FeatureTestTrait propagates PageNotFoundException instead of
 * returning a 404 response, so 404 routes are tested with expectException()
 * rather than assertSame(404, $result->getStatusCode()).
 *
 * NOTE: Property named $routeList (not $routes) because CIUnitTestCase
 * already declares a non-static $routes; PHP forbids redeclaring it as static.
 */
class PublicRoutesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /** @var array<array{0:string,1:int}> 200-only routes (404/302 tested separately) */
    private static array $routeList = [
        ['/',               200],
        ['/about',          200],
        ['/infrastructure', 200],
        ['/projects',       200],
        ['/clients',        200],
        ['/industries',     200],
        ['/careers',        200],
        ['/news',           200],
        ['/disclaimer',     200],
        ['/contact',        200],
        // Machines – 8 products
        ['/machines/tube-mill',        200],
        ['/machines/draw-bench',       200],
        ['/machines/pilger-mill',      200],
        ['/machines/rotary-swaging',   200],
        ['/machines/hydrotester',      200],
        ['/machines/straightening',    200],
        ['/machines/push-pointing',    200],
        ['/machines/press-entry-line', 200],
        // Machines – 4 solutions
        ['/machines/tube-cutting',       200],
        ['/machines/robot-automation',   200],
        ['/machines/material-handling',  200],
        ['/machines/gauging-inspection', 200],
        // Contact with machine prefill
        ['/contact?machine=tube-mill', 200],
    ];

    // ── 200 routes ────────────────────────────────────────────────────────────

    public function testAllPublicRoutesReturn200(): void
    {
        foreach (self::$routeList as [$uri, $expected]) {
            $result = $this->get($uri);
            $this->assertSame(
                $expected,
                $result->response()->getStatusCode(),
                "GET {$uri} expected HTTP {$expected}"
            );
        }
    }

    // ── Redirect ──────────────────────────────────────────────────────────────

    public function testLegacyCareerRedirects(): void
    {
        $result = $this->get('/career');
        $this->assertSame(302, $result->response()->getStatusCode());
    }

    // ── 404 routes — CI4 4.5 throws PageNotFoundException in test context ─────

    /**
     * Unmatched addresses go through App\Controllers\NotFound, which checks
     * the redirects table first, so a 404 now comes back as a response rather
     * than as an uncaught PageNotFoundException.
     */
    public function testUnknownPathReturns404(): void
    {
        $this->get('/this-page-does-not-exist')->assertStatus(404);
    }

    public function testUnknownMachineSlugReturns404(): void
    {
        $this->get('/machines/not-a-real-machine')->assertStatus(404);
    }

    public function testAnUnknownPathStillReaches404WhenTheRedirectsTableIsUnreadable(): void
    {
        // The test database has no redirects table; the lookup must fail
        // quietly and leave the visitor on a normal 404.
        $this->get('/no-such-page-at-all')->assertStatus(404);
    }

    // ── Content assertions ────────────────────────────────────────────────────

    public function testHomePageContainsTIMBBrand(): void
    {
        $result = $this->get('/');
        $result->assertStatus(200);
        $result->assertSee('TIMB');
    }

    public function testMachinePageContainsMachineName(): void
    {
        $result = $this->get('/machines/tube-mill');
        $result->assertStatus(200);
        $result->assertSee('Tube Mill');
    }

    public function testContactPageContainsEnquiryText(): void
    {
        $result = $this->get('/contact');
        $result->assertStatus(200);
        // assertSee in CI4 4.5 accepts only a string (no bool 2nd arg)
        $result->assertSee('enquiry');
    }

    public function testCareersPageContainsApplyText(): void
    {
        $result = $this->get('/careers');
        $result->assertStatus(200);
        $result->assertSee('apply');
    }
}
