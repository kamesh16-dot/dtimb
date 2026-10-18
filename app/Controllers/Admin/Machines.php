<?php

namespace App\Controllers\Admin;

use App\Models\MachineModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Machines CRUD. Everything saved here is what the public site renders —
 * the listings, the header menu and the /machines/<slug> pages.
 */
class Machines extends AdminController
{
    protected MachineModel $machines;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->machines = new MachineModel();
    }

    public function index(): string
    {
        $rows  = $this->machines->forAdmin();
        $pager = $this->listPage(count($rows));

        return $this->adminView('admin/machines/index', [
            'pageTitle' => 'Machines',
            'rows'      => $pager->slice($rows),
            'pager'     => $pager,
        ]);
    }

    public function create(): string
    {
        return $this->form([
            'kind'         => 'product',
            'show_in_menu' => 1,
            'is_published' => 1,
            'menu_order'   => $this->nextOrder('menu_order'),
            'nav_order'    => $this->nextOrder('nav_order'),
        ], 'Add Machine');
    }

    public function edit(int $id): string
    {
        $row = $this->machines->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->form($row, 'Edit Machine');
    }

    public function store(): RedirectResponse
    {
        $data = $this->formData();

        if (! $this->machines->insert($data)) {
            return $this->backWithErrors($this->machines->errors());
        }

        $this->audit('machine_create', $data['name'], (int) $this->machines->getInsertID(), 'machine');

        // Newest first, so the new machine is at the top of page 1.
        return $this->toList('/admin/machines', firstPage: true)->with('success', 'Machine created.');
    }

    public function update(int $id): RedirectResponse
    {
        $row = $this->machines->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        // The slug's is_unique rule carries an {id} placeholder so a machine
        // does not collide with itself. Model::update() fills placeholders from
        // the data it is given, so the id has to travel with it; it is not in
        // allowedFields, so it never reaches the UPDATE statement.
        $data = ['id' => $id] + $this->formData();

        if (! $this->machines->update($id, $data)) {
            return $this->backWithErrors($this->machines->errors());
        }

        $this->audit('machine_update', $data['name'], $id, 'machine');

        return $this->toList('/admin/machines')->with('success', 'Machine updated.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->machines->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->machines->delete($id); // soft delete — keeps details_json recoverable
        $this->audit('machine_delete', $row['name'], $id, 'machine');

        return $this->toList('/admin/machines')
                         ->with('success', 'Machine deleted. Its slug stays reserved.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $row = $this->machines->withDeleted()->find($id);
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->machines->restore($id);
        $this->audit('machine_restore', $row['name'], $id, 'machine');

        return $this->toList('/admin/machines')->with('success', 'Machine restored.');
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function form(array $row, string $title): string
    {
        return $this->adminView('admin/machines/form', [
            'pageTitle' => $title,
            'row'       => $row,
            'groups'    => $this->machines->groupNames(),
            'visuals'   => MachineModel::VISUALS,
            'sheets'    => $this->specSheets(),
            'images'    => array_keys(timb_manifest()),
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    /**
     * Read the editable columns off the form. details_json is deliberately not
     * in here: the rich product-page payload is not editable through this form,
     * so an update must leave whatever is already stored untouched.
     */
    private function formData(): array
    {
        $post = $this->request->getPost();

        return [
            'name'         => trim((string) ($post['name'] ?? '')),
            'slug'         => trim((string) ($post['slug'] ?? '')),
            'group_name'   => trim((string) ($post['group_name'] ?? '')),
            'kind'         => ($post['kind'] ?? 'product') === 'solution' ? 'solution' : 'product',
            'series_label' => trim((string) ($post['series_label'] ?? '')),
            'metric'       => trim((string) ($post['metric'] ?? '')),
            'summary'      => trim((string) ($post['summary'] ?? '')),
            'image_key'    => trim((string) ($post['image_key'] ?? '')) ?: null,
            'visual_key'   => trim((string) ($post['visual_key'] ?? '')) ?: null,
            'spec_sheet'   => trim((string) ($post['spec_sheet'] ?? '')) ?: null,
            'menu_order'   => (int) ($post['menu_order'] ?? 0),
            'nav_order'    => (int) ($post['nav_order'] ?? 0),
            'show_in_menu' => isset($post['show_in_menu']) ? 1 : 0,
            'is_published' => isset($post['is_published']) ? 1 : 0,
        ];
    }

    /** One past the current highest, so a new machine lands at the end. */
    private function nextOrder(string $column): int
    {
        $row = $this->machines->builder()->selectMax($column)->get()->getRowArray();

        return (int) ($row[$column] ?? -1) + 1;
    }

    /** Spec sheets available under public/spec-sheets/. */
    private function specSheets(): array
    {
        $files = glob(FCPATH . 'spec-sheets/*.html') ?: [];

        return array_map(static fn ($f) => basename($f, '.html'), $files);
    }
}
