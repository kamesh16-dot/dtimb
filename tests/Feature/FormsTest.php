<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests for the contact enquiry and career application forms.
 *
 * Covers: honeypot bypass, CSRF rejection, validation errors,
 * successful submission.
 *
 * CI4 4.5 note: CSRF violations throw SecurityException in test context
 * instead of returning a 403/redirect response, so we use expectException().
 */
class FormsTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    // ── Honeypot ──────────────────────────────────────────────────────────────

    public function testContactHoneypotFilledReturnsSilentSuccess(): void
    {
        $result = $this->withBody(http_build_query([
            'website' => 'http://spammy.example.com', // honeypot — bots fill this
            'name'    => 'Bot Name',
            'email'   => 'bot@example.com',
            'message' => 'Buy cheap pills',
            csrf_token() => csrf_hash(),
        ]))->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post('/contact/send');

        // Honeypot triggers silent redirect — pretends success
        $this->assertSame(302, $result->response()->getStatusCode());
        $location = (string) $result->response()->getHeader('Location');
        $this->assertStringContainsString('contact', $location);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function testContactFormValidationFailsOnEmptyData(): void
    {
        $result = $this->withBody(http_build_query([
            'website' => '',
            'name'    => '',
            'email'   => 'not-an-email',
            'message' => 'hi', // too short (min 10)
            csrf_token() => csrf_hash(),
        ]))->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post('/contact/send');

        // Validation failure → redirect back with errors
        $this->assertSame(302, $result->response()->getStatusCode());
    }

    public function testContactFormValidationPassesWithValidData(): void
    {
        $result = $this->withBody(http_build_query([
            'website' => '', // honeypot empty
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'phone'   => '+91 98765 43210',
            'company' => 'Acme Ltd',
            'interest'=> 'Tube Mill',
            'message' => 'This is a test enquiry message long enough to pass validation.',
            csrf_token() => csrf_hash(),
        ]))->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post('/contact/send');

        // Valid submission → redirect with form_ok flash
        $this->assertSame(302, $result->response()->getStatusCode());
        $location = (string) $result->response()->getHeader('Location');
        $this->assertStringContainsString('contact', $location);
    }

    public function testCareerFormValidationFailsWithoutResume(): void
    {
        $result = $this->withBody(http_build_query([
            'website' => '',
            'name'    => 'Job Seeker',
            'email'   => 'seeker@example.com',
            'phone'   => '9876543210',
            'area'    => 'Machine design',
            csrf_token() => csrf_hash(),
        ]))->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post('/careers/apply');

        // Resume required → validation fails → redirect back
        $this->assertSame(302, $result->response()->getStatusCode());
    }

    // ── CSRF ──────────────────────────────────────────────────────────────────

    /**
     * In CI4 4.5, FeatureTestTrait propagates SecurityException from the CSRF
     * filter rather than converting it to a 403 response. asserting the exception
     * IS the correct test — it confirms the CSRF filter fires as expected.
     */
    public function testContactFormWithoutCsrfTokenThrowsSecurityException(): void
    {
        $this->expectException(SecurityException::class);

        $this->withBody(http_build_query([
            'name'    => 'Test',
            'email'   => 'test@example.com',
            'message' => 'Test message here that is long enough',
            // No CSRF token included
        ]))->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post('/contact/send');
    }
}
