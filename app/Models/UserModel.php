<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'name', 'email', 'password_hash', 'role',
        'is_active', 'force_password_change',
        'last_login_at', 'failed_logins', 'locked_until',
    ];

    protected $hidden = ['password_hash'];

    // ── Auth helpers ──────────────────────────────────────────────────────────

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)
                    ->where('is_active', 1)
                    ->where('deleted_at IS NULL', null, false)
                    ->first();
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function recordLogin(int $id): void
    {
        $this->update($id, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'failed_logins' => 0,
            'locked_until'  => null,
        ]);
    }

    public function recordFailedLogin(int $id): void
    {
        $user = $this->find($id);
        $fails = ($user['failed_logins'] ?? 0) + 1;

        $data = ['failed_logins' => $fails];
        if ($fails >= 5) {
            $data['locked_until'] = date('Y-m-d H:i:s', time() + 900); // 15 min
        }
        $this->update($id, $data);
    }

    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }
        return strtotime($user['locked_until']) > time();
    }
}
