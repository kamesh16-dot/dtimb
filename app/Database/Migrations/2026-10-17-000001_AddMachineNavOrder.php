<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The header "Products" menu is ordered differently from the product/solution
 * listings (see Config\Site::$productMenu vs Config\Site::$products). The
 * original import kept only `menu_order` — the listing order — so the menu
 * order had nowhere to live. This adds it and backfills from the config.
 *
 *   menu_order → order in the product/solution listings (home, prev/next)
 *   nav_order  → order inside the header menu; groups sort by their lowest
 */
class AddMachineNavOrder extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('machines', [
            'nav_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
                'after'      => 'menu_order',
            ],
        ]);

        // Backfill from Config\Site::$productMenu so the live menu is unchanged.
        $i  = 0;
        $db = $this->db;
        foreach (config('Site')->productMenu as $slugs) {
            foreach ($slugs as $slug) {
                $db->table('machines')->where('slug', $slug)->update(['nav_order' => $i++]);
            }
        }

        // Anything not listed in the config menu goes after, in listing order.
        $rest = $db->table('machines')->select('id')->where('nav_order', 0)
                   ->where('slug !=', 'tube-mill')->orderBy('menu_order')->get()->getResultArray();
        foreach ($rest as $r) {
            $db->table('machines')->where('id', $r['id'])->update(['nav_order' => $i++]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('machines', 'nav_order');
    }
}
