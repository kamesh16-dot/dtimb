<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Loads the site's content — machines, pages, news, photos, lists, settings —
 * into a new, empty database, from app/Database/Seeds/data/content.json.
 *
 * Refuses to run on a database that already has machines, so it can never
 * overwrite a live site. The pages table is the one exception to "empty": the
 * SeedSitePages migration fills it with defaults, which this replaces.
 *
 *   php spark migrate
 *   php spark db:seed ImportExistingContentSeeder
 *
 * The file holds no users, messages or other personal data. To refresh it from
 * a live database, see docs/CMS-SETUP.md.
 */
class ImportExistingContentSeeder extends Seeder
{
    public function run(): void
    {
        $file = APPPATH . 'Database/Seeds/data/content.json';
        $data = json_decode((string) file_get_contents($file), true);
        if (! is_array($data)) {
            throw new \RuntimeException('Could not read ' . $file);
        }
        unset($data['_about']);

        if ($this->db->table('machines')->countAllResults() > 0) {
            $this->say('This database already has content — nothing imported. (The importer only fills an empty database.)', 'yellow');

            return;
        }

        $this->db->transStart();
        $this->db->table('pages')->truncate(); // the migration's defaults; replaced by the real pages below

        foreach ($data as $table => $rows) {
            if ($rows === [] || ! $this->db->tableExists($table)) {
                continue;
            }
            $columns = array_flip($this->db->getFieldNames($table));
            $rows    = array_map(static fn ($r) => array_intersect_key($r, $columns), $rows);
            foreach (array_chunk($rows, 50) as $chunk) {
                $this->db->table($table)->insertBatch($chunk);
            }
            $this->say(sprintf('  %-18s %d rows', $table, count($rows)));
        }
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('The import failed and was rolled back.');
        }

        $this->say('Content imported.', 'green');
    }

    private function say(string $text, ?string $color = null): void
    {
        if (is_cli()) {
            CLI::write($text, $color);
        }
    }
}
