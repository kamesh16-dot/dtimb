<?php

namespace Tests\Unit;

use App\Models\RedirectModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Stored paths are compared against the address a visitor asked for. If the
 * two are normalised differently the redirect simply never fires, with no
 * error anywhere — which is how this module sat broken.
 */
final class RedirectPathTest extends CIUnitTestCase
{
    public function testAPathGainsALeadingSlash(): void
    {
        $this->assertSame('/old-page', RedirectModel::normalise('old-page'));
        $this->assertSame('/old-page', RedirectModel::normalise('/old-page'));
    }

    public function testATrailingSlashIsRemoved(): void
    {
        $this->assertSame('/old-page', RedirectModel::normalise('/old-page/'));
        $this->assertSame('/old-page', RedirectModel::normalise('old-page/'));
    }

    public function testNestedPathsKeepTheirShape(): void
    {
        $this->assertSame('/products/tube-mill', RedirectModel::normalise('products/tube-mill/'));
    }

    public function testSurroundingWhitespaceIsIgnored(): void
    {
        $this->assertSame('/old-page', RedirectModel::normalise('  /old-page  '));
    }

    public function testAQueryStringIsStripped(): void
    {
        // The query is carried across at redirect time; storing it would stop
        // the path matching at all.
        $this->assertSame('/old-page', RedirectModel::normalise('/old-page?utm_source=x'));
        $this->assertSame('/old-page', RedirectModel::normalise('/old-page#section'));
    }

    public function testAnEmptyPathStaysEmptySoItCanBeRejected(): void
    {
        $this->assertSame('', RedirectModel::normalise(''));
        $this->assertSame('', RedirectModel::normalise('   '));
    }

    public function testAnAbsoluteUrlIsLeftAlone(): void
    {
        $this->assertSame('https://example.test/page', RedirectModel::normalise('https://example.test/page'));
        $this->assertSame('http://example.test', RedirectModel::normalise('http://example.test/'));
    }

    public function testExternalTargetsAreRecognised(): void
    {
        $this->assertTrue(RedirectModel::isExternal('https://example.test'));
        $this->assertTrue(RedirectModel::isExternal('HTTP://example.test'));
        $this->assertFalse(RedirectModel::isExternal('/machines/tube-mill'));
        $this->assertFalse(RedirectModel::isExternal('machines'));
    }

    public function testOnlyRealRedirectCodesAreOffered(): void
    {
        $this->assertSame([301, 302], array_keys(RedirectModel::CODES));
    }

    public function testTheFrontControllerIsStrippedFromARequestedPath(): void
    {
        // Whether index.php appears depends on the server's rewrite rules; a
        // path carrying it would never match a stored one.
        helper('site');

        $this->assertSame('/old-page', timb_path('/index.php/old-page'));
        $this->assertSame('/old-page', timb_path('/old-page'));
        $this->assertSame('/', timb_path('/index.php'));
        $this->assertSame('/', timb_path('/'));
    }
}
