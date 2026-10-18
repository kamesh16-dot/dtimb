<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Paginator;
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

    /**
     * Paginate the list this screen shows, and remember the page and filters
     * it is on so toList() can bring the user back to the same place after a
     * save, delete or restore.
     */
    protected function listPage(int $total, int $defaultPerPage = 20, string $pageParam = 'page', string $perParam = 'per'): Paginator
    {
        // "edit" opens an inline form; coming back after the save must not reopen it.
        parse_str((string) $this->request->getUri()->getQuery(), $query);
        unset($query['edit']);
        session()->set('admin_list_return.' . timb_path(), http_build_query($query));

        return Paginator::fromRequest($total, $defaultPerPage, $pageParam, $perParam);
    }

    /**
     * Redirect to a list screen, on the page and with the filters it was last
     * viewed with. $firstPage keeps the filters but starts from page 1 — for
     * lists sorted newest first, where something just added appears there.
     */
    protected function toList(string $path, bool $firstPage = false): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to($this->listUrl($path, $firstPage));
    }

    /**
     * A list screen's address with the page and filters it was last viewed
     * with — for redirects, and for Back and Cancel links on its forms.
     */
    protected function listUrl(string $path, bool $firstPage = false): string
    {
        $path  = '/' . trim($path, '/');
        $query = (string) (session()->get('admin_list_return.' . $path) ?? '');

        if ($firstPage) {
            parse_str($query, $params);
            unset($params['page']);
            $query = http_build_query($params);
        }

        return $path . ($query !== '' ? '?' . $query : '');
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
