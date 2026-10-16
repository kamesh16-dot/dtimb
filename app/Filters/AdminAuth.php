<?php

namespace App\Filters;

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

        // Forced password change
        $uri = (string) $request->getUri();
        if ($session->get('admin_force_password_change') && ! str_contains($uri, '/admin/change-password')) {
            return redirect()->to('/admin/change-password');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
