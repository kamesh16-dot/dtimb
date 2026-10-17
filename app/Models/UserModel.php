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

    /** Roles a user can hold, and how they are described in the admin. */
    public const ROLES = [
        'admin'  => 'Admin — full access, including users and settings',
        'editor' => 'Editor — content only',
    ];

    public const PASSWORD_MIN = 12;

    /**
     * Check a password against the policy. Shared by the Users screen and the
     * forced change at first login, so the two can never disagree.
     *
     * @return list<string> what is wrong, empty when the password is fine
     */
    public static function passwordProblems(string $password): array
    {
        $problems = [];

        if (strlen($password) < self::PASSWORD_MIN) {
            $problems[] = 'Password must be at least ' . self::PASSWORD_MIN . ' characters.';
        }
        if (! preg_match('/[A-Z]/', $password)) {
            $problems[] = 'Password must contain at least one uppercase letter.';
        }
        if (! preg_match('/[0-9]/', $password)) {
            $problems[] = 'Password must contain at least one number.';
        }
        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            $problems[] = 'Password must contain at least one symbol.';
        }

        return $problems;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // ── Admin helpers ─────────────────────────────────────────────────────────

    /** Live accounts, newest last. */
    public function listing(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    /** Accounts removed but still recoverable. */
    public function deletedListing(): array
    {
        return $this->onlyDeleted()->orderBy('id', 'ASC')->findAll();
    }

    /** How many active admins remain, optionally ignoring one account. */
    public function activeAdminCount(?int $ignoreId = null): int
    {
        $builder = $this->where('role', 'admin')->where('is_active', 1);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults();
    }

    /** True when changing this account would leave nobody able to administer. */
    public function isLastActiveAdmin(int $id): bool
    {
        $user = $this->find($id);
        if ($user === null || $user['role'] !== 'admin' || ! $user['is_active']) {
            return false;
        }

        return $this->activeAdminCount($id) === 0;
    }

    public function restore(int $id): bool
    {
        return $this->builder()->where('id', $id)->update(['deleted_at' => null]);
    }

    /** Clear a lockout after too many failed logins. */
    public function unlock(int $id): void
    {
        $this->update($id, ['failed_logins' => 0, 'locked_until' => null]);
    }

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
