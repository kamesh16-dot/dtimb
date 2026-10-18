<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Security headers on every response the app sends.
 *
 * The Content-Security-Policy lists exactly what the pages load from outside:
 * Google Fonts, and the Google Maps embed on the contact page. Anything else —
 * a script injected into CMS content, say — is refused by the browser.
 * Inline scripts and styles are still allowed: the templates use them.
 *
 * HSTS is sent only when the site's address is https, so a local http copy is
 * never told to insist on https.
 */
class SecurityHeaders implements FilterInterface
{
    public const CSP = "default-src 'self'; "
        . "script-src 'self' 'unsafe-inline'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com data:; "
        . "img-src 'self' data: blob:; "
        . "frame-src https://www.google.com; "
        . "connect-src 'self'; "
        . "object-src 'none'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'self'";

    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $headers = [
            'Content-Security-Policy' => self::CSP,
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'SAMEORIGIN',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=(), payment=()',
        ];

        if (str_starts_with(config('App')->baseURL, 'https://')) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->hasHeader($name)) {
                $response->setHeader($name, $value);
            }
        }

        return $response;
    }
}
