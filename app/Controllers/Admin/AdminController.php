<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Base class for all admin controllers.
 * Provides: $this->adminUser, audit(), requireRole(), view helpers.
 */
abstract class AdminController extends BaseController
{
    protected array $adminUser = [];
    protected string $adminPrefix = 'admin';

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->adminUser = session()->get('admin_user') ?? [];
    }

    /** Render an admin view inside the layout */
    protected function adminView(string $view, array $data = []): string
    {
        $data['adminUser']    = $this->adminUser;
        $data['pageTitle']    = $data['pageTitle'] ?? 'Dashboard';
        $data['currentRoute'] = current_url(true)->getPath();
        $data['content']      = view($view, $data);
        return view('admin/layout', $data);
    }

    /** Log an action to the audit_log table */
    protected function audit(string $action, string $summary = '', ?int $entityId = null, string $entity = ''): void
    {
        try {
            db_connect()->table('audit_log')->insert([
                'user_id'    => $this->adminUser['id'] ?? 0,
                'action'     => $action,
                'entity'     => $entity ?: strtok($action, '_'),
                'entity_id'  => $entityId,
                'summary'    => $summary,
                'ip'         => $this->request->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // audit failure must never break the request
            log_message('error', 'Audit write failed: ' . $e->getMessage());
        }
    }

    /** Abort with 403 if user doesn't have required role */
    protected function requireRole(string $role): void
    {
        $userRole = $this->adminUser['role'] ?? 'editor';
        if ($role === 'admin' && $userRole !== 'admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    /** Flash + redirect back */
    protected function backWithError(string $msg): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->back()->withInput()->with('error', $msg);
    }

    protected function backWithSuccess(string $msg): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->back()->with('success', $msg);
    }

    /** Flash per-field validation errors and redirect back with the input intact */
    protected function backWithErrors(array $errors): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->back()->withInput()
                         ->with('errors', $errors)
                         ->with('error', 'Please correct the highlighted fields.');
    }
}
