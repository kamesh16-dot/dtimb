<?php

namespace App\Database\Migrations;

use App\Models\MediaModel;
use CodeIgniter\Database\Migration;

/**
 * News photos lived outside the media library, as single files at
 * public/assets/img/news/<name>.webp. The library could not show them, say
 * where they were used, or offer them in the photo picker.
 *
 * This brings each one into the library under the name news articles already
 * use (news/imtex-2020-booth), so no article has to change: it makes the usual
 * -1600 and -640 sizes and adds a library row carrying the description the
 * article gave the photo. The original files are left where they are, and
 * photo_url() still falls back to them for anything not in the library.
 */
class ImportNewsPhotosToLibrary extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        // key => description, from every article and gallery row.
        $photos = [];
        foreach ($db->table('news_articles')->select('hero_path, hero_alt')->get()->getResultArray() as $a) {
            $photos[trim((string) $a['hero_path'], '/')] ??= trim((string) ($a['hero_alt'] ?? ''));
        }
        foreach ($db->table('news_gallery')->select('image_path, alt')->get()->getResultArray() as $g) {
            $photos[trim((string) $g['image_path'], '/')] ??= trim((string) ($g['alt'] ?? ''));
        }
        unset($photos['']);

        foreach ($photos as $key => $alt) {
            $source = FCPATH . 'assets/img/' . $key . '.webp';
            if (! is_file($source) || $db->table('media')->where('legacy_key', $key)->countAllResults() > 0) {
                continue;
            }

            try {
                $variants = MediaModel::writeVariants($source, $key);
            } catch (\Throwable $e) {
                log_message('error', "News photo {$key} not imported: " . $e->getMessage());
                continue;
            }

            $largest = $variants[(string) MediaModel::SIZES[0]] ?? reset($variants);
            $now     = date('Y-m-d H:i:s');

            $db->table('media')->insert([
                'legacy_key'    => $key,
                'original_name' => basename($source),
                'path'          => MediaModel::BASE . $key . '-' . MediaModel::SIZES[0] . '.webp',
                'mime'          => 'image/webp',
                'size'          => MediaModel::diskSize($key),
                'width'         => $largest[0],
                'height'        => $largest[1],
                'alt_text'      => $alt,
                'title'         => ucfirst(str_replace('-', ' ', basename($key))),
                'variants'      => json_encode($variants, JSON_UNESCAPED_SLASHES),
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        MediaModel::forgetManifest();
    }

    public function down(): void
    {
        $rows = $this->db->table('media')->like('legacy_key', 'news/', 'after')->get()->getResultArray();

        foreach ($rows as $r) {
            // Only the generated sizes; the original single files were never touched.
            foreach (MediaModel::variantFiles((string) $r['legacy_key']) as $file) {
                @unlink($file);
            }
            $this->db->table('media')->where('id', $r['id'])->delete();
        }

        MediaModel::forgetManifest();
    }
}
