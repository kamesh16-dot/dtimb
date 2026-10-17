<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;

class Auth extends AdminController
{
    protected UserModel $users;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->users = new UserModel();
    }

    // ── GET /admin/login ──────────────────────────────────────────────────────

    /**
     * Already signed in, this redirects rather than renders, so the return
     * type has to allow both. Declaring it `string` made visiting the login
     * page while signed in — a bookmark, or the back button — a 500.
     */
    public function login(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin');
        }
        return view('admin/auth/login', [
            'pageTitle' => 'Admin Login',
            'error'     => session()->getFlashdata('error'),
            'success'   => session()->getFlashdata('success'),
        ]);
    }

    // ── POST /admin/login ─────────────────────────────────────────────────────

    public function loginPost(): \CodeIgniter\HTTP\RedirectResponse
    {
        // Basic CSRF is on per route; just read the form
        $email    = trim($this->request->getPost('email') ?? '');
        $password = $this->request->getPost('password') ?? '';

        $user = $this->users->findByEmail($email);

        if (! $user) {
            return redirect()->to('/admin/login')->with('error', 'Invalid email or password.');
        }

        if ($this->users->isLocked($user)) {
            return redirect()->to('/admin/login')->with('error', 'Account locked. Try again in 15 minutes.');
        }

        if (! $this->users->verifyPassword($password, $user['password_hash'])) {
            $this->users->recordFailedLogin($user['id']);
            return redirect()->to('/admin/login')->with('error', 'Invalid email or password.');
        }

        // Success
        $this->users->recordLogin($user['id']);

        session()->set([
            'admin_logged_in'           => true,
            'admin_last_activity'       => time(),
            'admin_force_password_change' => (bool) $user['force_password_change'],
            'admin_user'                => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ]);

        $this->audit('login', 'Logged in');

        if ($user['force_password_change']) {
            return redirect()->to('/admin/change-password');
        }

        return redirect()->to('/admin');
    }

    // ── GET /admin/change-password ────────────────────────────────────────────

    public function changePassword(): string
    {
        return view('admin/auth/change_password', [
            'pageTitle' => 'Change Password',
            'adminUser' => session()->get('admin_user') ?? [],
            'error'     => session()->getFlashdata('error'),
        ]);
    }

    // ── POST /admin/change-password ───────────────────────────────────────────

    public function changePasswordPost(): \CodeIgniter\HTTP\RedirectResponse
    {
        $new     = $this->request->getPost('password') ?? '';
        $confirm = $this->request->getPost('password_confirm') ?? '';

        $problems = UserModel::passwordProblems($new);
        if ($problems !== []) {
            return redirect()->to('/admin/change-password')->with('error', $problems[0]);
        }
        if ($new !== $confirm) {
            return redirect()->to('/admin/change-password')->with('error', 'Passwords do not match.');
        }

        $userId = session()->get('admin_user')['id'] ?? null;
        if (! $userId) {
            return redirect()->to('/admin/login');
        }

        $this->users->update($userId, [
            'password_hash'         => UserModel::hash($new),
            'force_password_change' => 0,
        ]);

        session()->set('admin_force_password_change', false);
        $this->audit('password_change', 'Password changed');

        return redirect()->to('/admin')->with('success', 'Password changed successfully.');
    }

    // ── GET /admin/logout ─────────────────────────────────────────────────────

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->audit('logout', 'Logged out');
        session()->destroy();
        return redirect()->to('/admin/login')->with('success', 'You have been logged out.');
    }
}
