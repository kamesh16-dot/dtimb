<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The public forms reject posts with no valid "form opened at" stamp, and
 * treat a reply within seconds as a bot. A stamp that could be forged, or one
 * that never expires, would make both checks worthless.
 */
final class FormStampTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('site');
    }

    private function stampAt(int $time): string
    {
        return $time . '.' . timb_form_signature((string) $time);
    }

    public function testAFreshStampReadsBackAsAge(): void
    {
        preg_match('/value="([^"]+)"/', form_stamp(), $m);

        $this->assertSame(0, form_stamp_age(html_entity_decode($m[1])));
    }

    public function testAgeIsCountedFromWhenTheFormWasOpened(): void
    {
        $this->assertSame(30, form_stamp_age($this->stampAt(time() - 30)));
    }

    public function testAForgedOrChangedStampIsRejected(): void
    {
        $this->assertNull(form_stamp_age((time() - 30) . '.not-a-signature'));
        // Back-dating a real stamp breaks its signature.
        [$time, $sig] = explode('.', $this->stampAt(time()));
        $this->assertNull(form_stamp_age(($time - 60) . '.' . $sig));
    }

    public function testMissingOrMalformedStampsAreRejected(): void
    {
        foreach ([null, '', 'abc', '123', '.sig', 'x.y'] as $bad) {
            $this->assertNull(form_stamp_age($bad), var_export($bad, true));
        }
    }

    public function testAStampOlderThanADayExpires(): void
    {
        $this->assertNull(form_stamp_age($this->stampAt(time() - 86401)));
        $this->assertSame(86000, form_stamp_age($this->stampAt(time() - 86000)));
    }

    public function testAStampFromTheFutureIsRejected(): void
    {
        $this->assertNull(form_stamp_age($this->stampAt(time() + 600)));
    }

    public function testAdminUrlBuildsFromTheSiteAddress(): void
    {
        $base = rtrim(site_url(), '/') . '/admin';

        $this->assertSame($base, admin_url());
        $this->assertSame($base . '/machines', admin_url('machines'));
        // Stored paths that already carry /admin pass straight through.
        $this->assertSame($base . '/machines/edit/4', admin_url('/admin/machines/edit/4'));
        $this->assertSame($base . '/seo?tab=settings', admin_url('/admin/seo?tab=settings'));
        $this->assertSame($base, admin_url('/admin'));
        // A trailing slash is kept, so an id can be appended.
        $this->assertSame($base . '/machines/edit/', admin_url('machines/edit/'));
    }

    public function testSafeInlineKeepsOnlyPlainFormatting(): void
    {
        $this->assertSame('core machine<br>platforms', safe_inline('core machine<br>platforms'));
        $this->assertSame('<strong>12 m</strong>', safe_inline('<strong>12 m</strong>'));
        $this->assertStringNotContainsString('<script', safe_inline('<script>alert(1)</script>'));
        $this->assertStringNotContainsString('<img', safe_inline('<img src=x onerror=alert(1)>'));
        $this->assertStringNotContainsString('onclick="', safe_inline('<b onclick="x()">x</b>'));
    }
}
