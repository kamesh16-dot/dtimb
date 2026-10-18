<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Creates the first admin account on a new install, with a random password
 * that is printed once and must be changed at the first sign-in.
 *
 *   php spark db:seed AdminUserSeeder
 *   CMS_ADMIN_EMAIL=you@timbindia.com php spark db:seed AdminUserSeeder
 *
 * Does nothing if any account already exists.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->db->table('users')->countAllResults() > 0) {
            $this->say('Accounts already exist — no admin created. Use Admin → Users to add one.', 'yellow');

            return;
        }

        $email    = (string) (getenv('CMS_ADMIN_EMAIL') ?: 'admin@timbindia.com');
        $password = $this->password();
        $now      = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'name'                  => 'Administrator',
            'email'                 => $email,
            'password_hash'         => UserModel::hash($password),
            'role'                  => 'admin',
            'is_active'             => 1,
            'force_password_change' => 1,
            'failed_logins'         => 0,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        $this->say('');
        $this->say('  Admin account created — write the password down now, it is not shown again:', 'green');
        $this->say('    Sign in at /admin/login');
        $this->say('    Email:    ' . $email);
        $this->say('    Password: ' . $password);
        $this->say('  You will be asked to choose a new password at the first sign-in.');
        $this->say('');
    }

    /**
     * 16 characters that always meet the password policy: at least one
     * uppercase letter, number and symbol by construction (no retry loop),
     * from an alphabet without look-alikes such as l/1 and O/0.
     */
    private function password(): string
    {
        $sets = [
            'upper'  => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'lower'  => 'abcdefghjkmnpqrstuvwxyz',
            'digit'  => '23456789',
            'symbol' => '-_.!@#%+=',
        ];
        $pick = static fn (string $set) => $set[random_int(0, strlen($set) - 1)];

        $chars = [$pick($sets['upper']), $pick($sets['digit']), $pick($sets['symbol'])];
        $all   = implode('', $sets);
        while (count($chars) < 16) {
            $chars[] = $pick($all);
        }

        // Fisher–Yates with random_int, so the required characters are not always first.
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        $password = implode('', $chars);
        if (UserModel::passwordProblems($password) !== []) {
            throw new \LogicException('Generated password does not meet the policy — the policy has changed; update AdminUserSeeder.');
        }

        return $password;
    }

    private function say(string $text, ?string $color = null): void
    {
        if (is_cli()) {
            CLI::write($text, $color);
        }
    }
}
