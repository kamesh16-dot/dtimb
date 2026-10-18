<?php

namespace App\Libraries;

/**
 * Cache for the content the public pages read on every request — settings,
 * machines, lists, pages, the image index, news — so a page view costs a few
 * file reads instead of about seventeen database queries.
 *
 * Every save in the admin goes through AdminController::audit(), which calls
 * forget(), so an edit shows on the site straight away. The TTL only bounds
 * changes made outside the admin (a migration, a seeder, a hand edit).
 * `php spark cms:clear-cache` empties it too.
 *
 * Only successful reads are cached: a read that throws (the database is down)
 * is never stored, so the site's fallbacks still take over and recover on
 * their own. Off under tests, which change the database between requests.
 */
final class CmsCache
{
    private const PREFIX = 'cms_';
    private const TTL    = 600;

    /**
     * @template T
     * @param callable(): T $read
     * @return T
     */
    public static function remember(string $key, callable $read)
    {
        if (ENVIRONMENT === 'testing') {
            return $read();
        }

        $name = self::PREFIX . preg_replace('/[^A-Za-z0-9_.-]/', '_', $key);

        try {
            $hit = cache()->get($name);
        } catch (\Throwable $e) {
            $hit = null; // an unreadable cache must never take the page down
        }
        if ($hit !== null) {
            return $hit;
        }

        $value = $read();

        try {
            cache()->save($name, $value, self::TTL);
        } catch (\Throwable $e) {
            log_message('warning', 'CMS cache write failed: ' . $e->getMessage());
        }

        return $value;
    }

    /** Drop everything cached, after any change made in the admin. */
    public static function forget(): void
    {
        try {
            cache()->deleteMatching(self::PREFIX . '*');
        } catch (\Throwable $e) {
            log_message('warning', 'CMS cache could not be cleared: ' . $e->getMessage());
        }
    }
}
