<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Admin accounts.
 *
 * Admin only, throughout. Two rules protect the installation from being
 * locked out: nobody can remove or disable their own account, and the last
 * active admin cannot be removed, disabled or demoted.
 */
class Users extends AdminController
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

    public function index(): string
    {
        $this->requireRole('admin');

        return $this->adminView('admin/users/index', [
            'pageTitle' => 'Users',
            'rows'      => $this->users->listing(),
            'deleted'   => $this->users->deletedListing(),
            'meId'      => (int) ($this->adminUser['id'] ?? 0),
            'adminCount' => $this->users->activeAdminCount(),
        ]);
    }

    public function create(): string
    {
        $this->requireRole('admin');

        return $this->form([], 'Add User');
    }

    public function edit(int $id): string
    {
        $this->requireRole('admin');

        $row = $this->users->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->form($row, 'Edit User');
    }

    public function store(): RedirectResponse
    {
        $this->requireRole('admin');

        $post     = $this->request->getPost();
        $name     = trim((string) ($post['name'] ?? ''));
        $email    = strtolower(trim((string) ($post['email'] ?? '')));
        $password = (string) ($post['password'] ?? '');
        $role     = $this->role($post['role'] ?? '');

        $errors = $this->basicProblems($name, $email);
        foreach (UserModel::passwordProblems($password) as $i => $problem) {
            $errors['password' . ($i ?: '')] = $problem;
        }
        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        $this->users->insert([
            'name'                  => $name,
            'email'                 => $email,
            'password_hash'         => UserModel::hash($password),
            'role'                  => $role,
            'is_active'             => 1,
            'force_password_change' => 1,
            'failed_logins'         => 0,
        ]);

        $this->audit('user_create', $email, (int) $this->users->getInsertID(), 'user');

        return redirect()->to('/admin/users')
                         ->with('success', $name . ' can now sign in, and will be asked to set a new password.');
    }

    public function update(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->users->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post   = $this->request->getPost();
        $name   = trim((string) ($post['name'] ?? ''));
        $email  = strtolower(trim((string) ($post['email'] ?? '')));
        $role   = $this->role($post['role'] ?? '');
        $active = isset($post['is_active']) ? 1 : 0;

        $errors = $this->basicProblems($name, $email, $id);

        // Losing the last admin would leave nobody able to manage the site.
        if ($errors === [] && $this->users->isLastActiveAdmin($id) && ($role !== 'admin' || ! $active)) {
            $errors['role'] = 'This is the only active admin. Give someone else admin access first.';
        }
        if ($errors === [] && $id === (int) ($this->adminUser['id'] ?? 0) && ! $active) {
            $errors['is_active'] = 'You cannot switch off your own account.';
        }

        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        $this->users->update($id, [
            'name'      => $name,
            'email'     => $email,
            'role'      => $role,
            'is_active' => $active,
        ]);

        $this->audit('user_update', $email, $id, 'user');

        return redirect()->to('/admin/users')->with('success', 'Account updated.');
    }

    /** Set a new password and require the holder to change it at next sign-in. */
    public function resetPassword(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->users->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $password = (string) $this->request->getPost('password');
        $problems = UserModel::passwordProblems($password);
        if ($problems !== []) {
            return $this->backWithErrors(['password' => $problems[0]]);
        }

        $this->users->update($id, [
            'password_hash'         => UserModel::hash($password),
            'force_password_change' => 1,
            'failed_logins'         => 0,
            'locked_until'          => null,
        ]);

        $this->audit('user_password_reset', (string) $row['email'], $id, 'user');

        return redirect()->to('/admin/users')
                         ->with('success', 'Password set. ' . $row['name'] . ' must change it at next sign-in.');
    }

    /** Clear a lockout caused by repeated failed sign-ins. */
    public function unlock(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->users->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->users->unlock($id);
        $this->audit('user_unlock', (string) $row['email'], $id, 'user');

        return redirect()->to('/admin/users')->with('success', 'Account unlocked.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->users->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($id === (int) ($this->adminUser['id'] ?? 0)) {
            return redirect()->to('/admin/users')->with('error', 'You cannot remove your own account.');
        }
        if ($this->users->isLastActiveAdmin($id)) {
            return redirect()->to('/admin/users')
                             ->with('error', 'This is the only active admin. Give someone else admin access first.');
        }

        $this->users->delete($id);
        $this->audit('user_delete', (string) $row['email'], $id, 'user');

        return redirect()->to('/admin/users')
                         ->with('success', $row['name'] . ' removed. The account can be restored.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->users->withDeleted()->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->users->restore($id);
        $this->audit('user_restore', (string) $row['email'], $id, 'user');

        return redirect()->to('/admin/users')->with('success', $row['name'] . ' restored.');
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function form(array $row, string $title): string
    {
        return $this->adminView('admin/users/form', [
            'pageTitle' => $title,
            'row'       => $row,
            'roles'     => UserModel::ROLES,
            'meId'      => (int) ($this->adminUser['id'] ?? 0),
            'lastAdmin' => $row !== [] && $this->users->isLastActiveAdmin((int) $row['id']),
            'minLength' => UserModel::PASSWORD_MIN,
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    /** @return array<string,string> */
    private function basicProblems(string $name, string $email, ?int $ignoreId = null): array
    {
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'A name is required.';
        }
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } else {
            $clash = $this->users->withDeleted()->where('email', $email);
            if ($ignoreId !== null) {
                $clash->where('id !=', $ignoreId);
            }
            if ($clash->first() !== null) {
                $errors['email'] = 'Another account already uses that email address.';
            }
        }

        return $errors;
    }

    private function role(string $role): string
    {
        return array_key_exists($role, UserModel::ROLES) ? $role : 'editor';
    }
}
