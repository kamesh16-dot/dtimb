<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The pages table held rows for only two of the site's ten pages, so the
 * admin list was a hard-coded array of labels rather than data, and the SEO
 * title and description of every page lived in App\Controllers\Pages.
 *
 * This seeds a row per public page using exactly the title and description
 * those controllers already produce, so nothing visible changes, and moves
 * each page's lead paragraph out of body_blocks into the intro column, which
 * existed but was never populated.
 */
class SeedSitePages extends Migration
{
    /**
     * key => [title, seo_title, seo_description]
     * Taken verbatim from App\Controllers\Pages and the layout's defaults.
     */
    private const PAGES = [
        'home' => [
            'Home',
            'TIMB | Precision Industrial Machinery',
            'TIMB engineers metal-forming, tube processing and testing machinery for industrial production.',
        ],
        'about' => [
            'About',
            'About | TIMB',
            'TI Machine Building: a unit of Tube Investments of India and the Murugappa Group, building custom machines, automation and tooling since 2012.',
        ],
        'infrastructure' => [
            'Infrastructure',
            'Infrastructure | TIMB',
            'Design tools, assembly, inspection and laboratory facilities behind every TIMB machine.',
        ],
        'projects' => [
            'Projects',
            'Projects | TIMB',
            'Completed projects from TIMB: tube mills, straightening, swaging, test rigs, vision inspection and line automation.',
        ],
        'clients' => [
            'Clients & FAQ',
            'Clients & FAQ | TIMB',
            'Who TIMB builds for, and answers to common questions about quotations, documents and services.',
        ],
        'industries' => [
            'Industries',
            'Industries | TIMB',
            'Automotive, railways, tube processing, EV, and heavy engineering: where TIMB machines work.',
        ],
        'careers' => [
            'Careers',
            'Careers | TIMB',
            'Work on custom machines, automation and tooling at TI Machine Building.',
        ],
        'news' => [
            'Latest News',
            'Latest News | TIMB',
            'News from TI Machine Building.',
        ],
        'contact' => [
            'Contact Us',
            'Contact Us | TIMB',
            'Contact TI Machine Building: works, corporate office, phone numbers and an enquiry form.',
        ],
        'disclaimer' => [
            'Disclaimer',
            'Disclaimer | TIMB',
            'Website disclaimer for TI Machine Building.',
        ],
    ];

    public function up(): void
    {
        $db  = $this->db;
        $now = date('Y-m-d H:i:s');

        foreach (self::PAGES as $key => [$title, $seoTitle, $seoDescription]) {
            $existing = $db->table('pages')->where('key', $key)->get()->getRowArray();

            if ($existing === null) {
                $db->table('pages')->insert([
                    'key'             => $key,
                    'title'           => $title,
                    'seo_title'       => $seoTitle,
                    'seo_description' => $seoDescription,
                    'is_published'    => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                continue;
            }

            // Existing rows already carry a seo_title; only fill what is empty.
            $fill = [];
            if (($existing['seo_description'] ?? null) === null || $existing['seo_description'] === '') {
                $fill['seo_description'] = $seoDescription;
            }
            if (($existing['seo_title'] ?? '') === '') {
                $fill['seo_title'] = $seoTitle;
            }
            if ($fill !== []) {
                $fill['updated_at'] = $now;
                $db->table('pages')->where('key', $key)->update($fill);
            }
        }

        $this->moveLeadIntoIntro();
    }

    /**
     * body_blocks.lead is the page's opening paragraph. Holding it inside the
     * block payload means it cannot be edited without editing raw JSON, while
     * the intro column sat unused. Move it across.
     */
    private function moveLeadIntoIntro(): void
    {
        foreach ($this->db->table('pages')->get()->getResultArray() as $row) {
            $blocks = json_decode((string) $row['body_blocks'], true);
            if (! is_array($blocks) || ! isset($blocks['lead'])) {
                continue;
            }

            $lead = (string) $blocks['lead'];
            unset($blocks['lead']);

            $this->db->table('pages')->where('id', $row['id'])->update([
                'intro'       => $lead,
                'body_blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down(): void
    {
        // Put the lead back where it was, then drop the seeded rows.
        foreach ($this->db->table('pages')->get()->getResultArray() as $row) {
            if (($row['intro'] ?? '') === '' || $row['intro'] === null) {
                continue;
            }
            $blocks = json_decode((string) $row['body_blocks'], true);
            if (! is_array($blocks)) {
                continue;
            }
            $this->db->table('pages')->where('id', $row['id'])->update([
                'body_blocks' => json_encode(['lead' => $row['intro']] + $blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'intro'       => null,
            ]);
        }

        $this->db->table('pages')
                 ->whereIn('key', array_diff(array_keys(self::PAGES), ['about', 'infrastructure']))
                 ->delete();
    }
}
