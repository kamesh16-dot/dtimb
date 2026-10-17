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
            'hint'    => 'Used for any page that has no description of its own.',
        ],
        'canonical_base' => [
            'label'   => 'Public address',
            'type'    => 'text',
            'default' => '',
            'hint'    => 'Overrides the public address from Settings. Leave blank to use that one.',
        ],
        'share_image' => [
            'label'   => 'Sharing image',
            'type'    => 'text',
            'default' => 'tube-mill/mill-1',
            'hint'    => 'Shown when a page is shared on LinkedIn or WhatsApp. An image key from the Media Library.',
        ],
        'social_tags' => [
            'label'   => 'Sharing previews',
            'type'    => 'bool',
            'default' => '1',
            'hint'    => 'Add Open Graph and Twitter tags so shared links show a title, description and image.',
        ],
        'canonical_tags' => [
            'label'   => 'Canonical links',
            'type'    => 'bool',
            'default' => '1',
            'hint'    => 'Tell search engines the single correct address for each page.',
        ],
        'allow_indexing' => [
            'label'   => 'Allow search engines',
            'type'    => 'bool',
            'default' => '1',
            'hint'    => 'Turn off to ask every search engine to stay away. Use only for a staging site.',
        ],
        'robots_extra' => [
            'label'   => 'Extra robots.txt rules',
            'type'    => 'textarea',
            'default' => '',
            'hint'    => 'Added to the generated robots.txt, one rule per line. Leave blank unless you know you need it.',
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
