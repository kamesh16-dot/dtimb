<?php

namespace App\Models;

use App\Libraries\CmsCache;
/**
 * Site identity — the name, tagline, contact email and links the layout
 * renders on every page.
 *
 * Stored in the `settings` table under the "site" group. Every key has a
 * default taken from Config\Site, which stays as the fallback when the table
 * cannot be read, so the site never renders a blank brand.
 */
class SiteSettingModel
{
    public const GROUP = 'site';

    /**
     * key => [label, type, property, hint]
     *
     * `property` is the matching field on Config\Site, which is both where the
     * default comes from and what gets replaced at render time.
     */
    public const FIELDS = [
        'name' => [
            'label'    => 'Site name',
            'type'     => 'text',
            'property' => 'name',
            'required' => true,
            'hint'     => 'The short name in the header, the footer and every browser title.',
        ],
        'tagline' => [
            'label'    => 'Tagline',
            'type'     => 'text',
            'property' => 'tagline',
            'required' => true,
            'hint'     => 'Shown under the name in the header and footer.',
        ],
        'email' => [
            'label'    => 'Contact email',
            'type'     => 'email',
            'property' => 'email',
            'required' => true,
            'hint'     => 'Shown across the site. Enquiry emails go here too, unless email.recipients in .env names someone else.',
        ],
        'linkedin' => [
            'label'    => 'LinkedIn page',
            'type'     => 'url',
            'property' => 'linkedin',
            'required' => false,
            'hint'     => 'The icon in the footer links here.',
        ],
        'website' => [
            'label'    => 'Public address',
            'type'     => 'url',
            'property' => 'website',
            'required' => false,
            'hint'     => 'The address the live site is served from. Used for canonical links and the sitemap unless SEO overrides it.',
        ],
        'footerTagline' => [
            'label'    => 'Footer line',
            'type'     => 'text',
            'property' => 'footerTagline',
            'required' => false,
            'hint'     => 'The sentence under the brand in the footer.',
        ],
    ];

    /** @var array<string,string>|null per-request cache */
    private static ?array $cache = null;

    /** Every setting, with Config\Site's values as the defaults. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $site   = config('Site');
        $values = [];
        foreach (self::FIELDS as $key => $field) {
            $values[$key] = (string) ($site->{$field['property']} ?? '');
        }

        try {
            $rows = CmsCache::remember('settings_' . self::GROUP, static fn () => db_connect()->table('settings')->where('group', self::GROUP)->get()->getResultArray());
            foreach ($rows as $r) {
                if (array_key_exists($r['key'], $values) && trim((string) $r['value']) !== '') {
                    $values[$r['key']] = (string) $r['value'];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Site settings unavailable, using Config\\Site: ' . $e->getMessage());
        }

        return self::$cache = $values;
    }

    public static function get(string $key): string
    {
        return self::all()[$key] ?? '';
    }

    /** Replace the matching fields on the shared Config\Site instance. */
    public static function applyToSite(object $site): void
    {
        foreach (self::all() as $key => $value) {
            $property = self::FIELDS[$key]['property'];
            if ($value !== '') {
                $site->{$property} = $value;
            }
        }
    }

    /**
     * Check submitted values.
     *
     * @return array<string,string> key => error, empty when they are fine
     */
    public static function problems(array $values): array
    {
        $errors = [];

        foreach (self::FIELDS as $key => $field) {
            $value = trim((string) ($values[$key] ?? ''));

            if ($value === '') {
                if (! empty($field['required'])) {
                    $errors[$key] = $field['label'] . ' is required.';
                }

                continue;
            }

            if ($field['type'] === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$key] = $field['label'] . ' must be a valid email address.';
            }

            if ($field['type'] === 'url' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[$key] = $field['label'] . ' must be a full web address, starting with https://.';
            }
        }

        return $errors;
    }

    public static function save(array $values): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        foreach (self::FIELDS as $key => $field) {
            $value  = trim((string) ($values[$key] ?? ''));
            $exists = $db->table('settings')->where('key', $key)->where('group', self::GROUP)->countAllResults();

            if ($exists) {
                $db->table('settings')->where('key', $key)->where('group', self::GROUP)
                   ->update(['value' => $value, 'updated_at' => $now]);
            } else {
                $db->table('settings')->insert([
                    'key' => $key, 'value' => $value, 'group' => self::GROUP,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        self::$cache = null;
    }

    public static function forget(): void
    {
        self::$cache = null;
    }
}
