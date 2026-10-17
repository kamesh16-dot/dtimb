<?php

namespace App\Controllers\Admin;

use App\Models\RedirectModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Redirects for addresses the site no longer serves.
 *
 * These only apply when nothing else matches the address, so a redirect
 * cannot take over a page that still exists.
 */
class Redirects extends AdminController
{
    protected RedirectModel $redirects;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->redirects = new RedirectModel();
    }

    public function index(): string
    {
        $rows = $this->redirects->allOrdered();

        $editId = (int) ($this->request->getGet('edit') ?? 0);
        $edit   = $editId > 0 ? $this->redirects->find($editId) : null;

        return $this->adminView('admin/redirects/index', [
            'pageTitle' => 'Redirects',
            'rows'      => $rows,
            'edit'      => $edit,
            'chained'   => $this->redirects->chained(),
            'codes'     => RedirectModel::CODES,
            'totalHits' => array_sum(array_column($rows, 'hits')),
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store(): RedirectResponse
    {
        [$from, $to, $code] = $this->input();

        $errors = $this->redirects->problems($from, $to);
        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        $this->redirects->insert([
            'from_path' => RedirectModel::normalise($from),
            'to_path'   => RedirectModel::normalise($to),
            'code'      => $code,
            'hits'      => 0,
        ]);

        $this->audit('redirect_create', $from . ' → ' . $to, (int) $this->redirects->getInsertID(), 'redirect');

        return redirect()->to('/admin/redirects')->with('success', 'Redirect added.');
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->redirects->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$from, $to, $code] = $this->input();

        $errors = $this->redirects->problems($from, $to, $id);
        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        $this->redirects->update($id, [
            'from_path' => RedirectModel::normalise($from),
            'to_path'   => RedirectModel::normalise($to),
            'code'      => $code,
        ]);

        $this->audit('redirect_update', $from . ' → ' . $to, $id, 'redirect');

        return redirect()->to('/admin/redirects')->with('success', 'Redirect saved.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->redirects->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->redirects->delete($id);
        $this->audit('redirect_delete', (string) $row['from_path'], $id, 'redirect');

        return redirect()->to('/admin/redirects')->with('success', 'Redirect deleted.');
    }

    /** @return array{0:string,1:string,2:int} */
    private function input(): array
    {
        $post = $this->request->getPost();
        $code = (int) ($post['code'] ?? 301);

        return [
            trim((string) ($post['from_path'] ?? '')),
            trim((string) ($post['to_path'] ?? '')),
            array_key_exists($code, RedirectModel::CODES) ? $code : 301,
        ];
    }
}
