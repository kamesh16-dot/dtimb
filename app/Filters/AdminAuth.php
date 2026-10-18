<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminAuth filter — redirects unauthenticated requests to /admin/login.
 * Also enforces idle timeout (30 min) and forced-password-change redirect.
 */
class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('admin_logged_in')) {
            return redirect()->to('/admin/login')->with('error', 'Please log in.');
        }

        // Idle timeout – 30 minutes
        $lastActivity = $session->get('admin_last_activity');
        if ($lastActivity && (time() - $lastActivity) > 1800) {
            $session->destroy();
            return redirect()->to('/admin/login')->with('error', 'Session timed out. Please log in again.');
        }
        $session->set('admin_last_activity', time());

        // The account as it is now, not as it was at sign-in: a user who has
        // been deactivated or removed is signed out on their next click, and a
        // change of role or name takes effect straight away.
        $current = $this->currentUser((int) ($session->get('admin_user')['id'] ?? 0));
        if ($current === null) {
            $session->destroy();

            return redirect()->to('/admin/login')->with('error', 'Your account is no longer active. Ask an admin if you need access.');
        }
        $session->set('admin_user', [
            'id'    => (int) $current['id'],
            'name'  => $current['name'],
            'email' => $current['email'],
            'role'  => $current['role'],
        ]);

        // Forced password change
        $uri = (string) $request->getUri();
        if ($session->get('admin_force_password_change') && ! str_contains($uri, '/admin/change-password')) {
            return redirect()->to('/admin/change-password');
        }
    }

    /** The live, active account for this id, or null. */
    private function currentUser(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        try {
            $user = (new UserModel())->where('is_active', 1)->find($id); // soft-deleted rows are excluded

            return is_array($user) ? $user : null;
        } catch (\Throwable $e) {
            // If the users table cannot be read, keep the session rather than
            // lock everyone out; the screens themselves will report the problem.
            log_message('error', 'Admin session check failed: ' . $e->getMessage());

            return session('admin_user');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
