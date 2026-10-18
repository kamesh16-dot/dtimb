<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Everything a new install needs after `php spark migrate`:
 *   php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ImportExistingContentSeeder::class);
        $this->call(AdminUserSeeder::class);
    }
}
