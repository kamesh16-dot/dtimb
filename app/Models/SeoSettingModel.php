<?php

namespace App\Models;

/**
 * Site-wide SEO settings, stored in the `settings` table under the "seo"
 * group so they sit alongside the existing site settings.
 *
 * Every key has a default here, so the site behaves sensibly before anything
 * is saved and keeps working if the table cannot be read.
 */
class SeoSettingModel
{
    public const GROUP = 'seo';

    /** key => [label, type, default, hint] */
    public const FIELDS = [
        'default_description' => [
            'label'   => 'Default description',
            'type'    => 'textarea',
            'default' => 'TIMB engineers metal-forming, tube processing and testing machinery for industrial production.',
            'hint'    => 'The sentence Google and link previews show for any page that has no description of its own. Aim for 70–160 characters.',
        ],
        'canonical_base' => [
            'label'   => 'Website address',
            'type'    => 'text',
            'default' => '',
            'hint'    => 'The address search engines should use, e.g. https://www.timbindia.com. Leave blank to use the one in Settings.',
        ],
        'share_image' => [
            'label'   => 'Picture for shared links',
            'type'    => 'text',
            'default' => 'tube-mill/mill-1',
            'hint'    => 'The photo shown when someone shares a link to the site. Start typing to pick one from the Media Library.',
        ],
        'social_tags' => [
            'label'   => 'Show a preview when a link is shared',
            'type'    => 'bool',
            'default' => '1',
            'hint'    => 'Links shared on LinkedIn, WhatsApp or Facebook show the page title, description and a picture instead of a bare address.',
        ],
        'canonical_tags' => [
            'label'   => 'Tell search engines each page’s main address',
            'type'    => 'bool',
            'default' => '1',
            'hint'    => 'Stops Google treating the same page at two addresses (with and without “www”, say) as two pages. Leave on.',
        ],
        'allow_indexing' => [
            'label'   => 'Let Google and other search engines list this website',
            'type'    => 'bool',
            'default' => '1',
            'hint'    => 'Keep this on for the live site. Turning it off asks every search engine to remove the site from its results — only for a test copy.',
        ],
        'robots_extra' => [
            'label'   => 'Extra rules for search engines (robots.txt)',
            'type'    => 'textarea',
            'default' => '',
            'hint'    => 'Technical. Added to robots.txt, one rule per line. Leave blank unless your developer asks for something here.',
        ],
    ];

    /** @var array<string,string>|null per-request cache */
    private static ?array $cache = null;

    /** Every setting, with defaults filled in. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $values = array_map(static fn ($f) => $f['default'], self::FIELDS);

        try {
            $rows = db_connect()->table('settings')->where('group', self::GROUP)->get()->getResultArray();
            foreach ($rows as $r) {
                if (array_key_exists($r['key'], $values)) {
                    $values[$r['key']] = (string) $r['value'];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'SEO settings unavailable, using defaults: ' . $e->getMessage());
        }

        return self::$cache = $values;
    }

    public static function get(string $key): string
    {
        return self::all()[$key] ?? (self::FIELDS[$key]['default'] ?? '');
    }

    public static function on(string $key): bool
    {
        return self::get($key) === '1';
    }

    /** Save the known keys. Unknown keys are ignored. */
    public static function save(array $values): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        foreach (self::FIELDS as $key => $field) {
            $value = $field['type'] === 'bool'
                ? (isset($values[$key]) ? '1' : '0')
                : trim((string) ($values[$key] ?? ''));

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

    /**
     * The site's public address without a trailing slash, or '' when unset.
     *
     * Falls back to the address on the Settings screen, so the domain only
     * has to be typed in one place; the field here is the override.
     */
    public static function canonicalBase(): string
    {
        $base = rtrim(self::get('canonical_base'), '/');
        if ($base !== '') {
            return $base;
        }

        try {
            return rtrim(SiteSettingModel::get('website'), '/');
        } catch (\Throwable $e) {
            log_message('error', 'Could not read the public address: ' . $e->getMessage());

            return '';
        }
    }
}
