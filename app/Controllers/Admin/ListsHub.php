<?php

namespace App\Controllers\Admin;

use App\Models\ListItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * The Lists Hub: CRUD over the small repeating content sets described in
 * Config\Lists. One controller serves every list.
 */
class ListsHub extends AdminController
{
    public function index(): string
    {
        $cards = [];
        foreach (ListItemModel::definitions() as $key => $def) {
            $list    = ListItemModel::make($key);
            $count   = null;
            $active  = null;
            try {
                $count  = $list->count();
                $active = count($list->active());
            } catch (\Throwable $e) {
                log_message('error', "List '{$key}' unreadable: " . $e->getMessage());
            }
            $cards[] = [
                'key'         => $key,
                'label'       => $def['label'],
                'description' => $def['description'],
                'consumer'    => $def['consumer'],
                'count'       => $count,
                'active'      => $active,
            ];
        }

        return $this->adminView('admin/lists/index', [
            'pageTitle' => 'Lists Hub',
            'cards'     => $cards,
        ]);
    }

    public function manage(string $key): string
    {
        $list = $this->list($key);

        $rows = [];
        foreach ($list->all() as $row) {
            $rows[] = ['id' => (int) $row['id'], 'is_active' => (int) $row['is_active']]
                    + ['values' => $list->values($row)];
        }

        $editId = (int) ($this->request->getGet('edit') ?? 0);
        $edit   = null;
        if ($editId > 0) {
            $row = $list->find($editId);
            if ($row !== null) {
                $edit = ['id' => $editId, 'values' => $list->values($row)];
            }
        }

        return $this->adminView('admin/lists/manage', [
            'pageTitle' => $list->label(),
            'list'      => $list,
            'def'       => $list->def(),
            'rows'      => $rows,
            'edit'      => $edit,
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store(string $key): RedirectResponse
    {
        $list   = $this->list($key);
        $values = $this->input($list);

        $errors = $list->validate($values);
        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        $id = $list->create($values);
        $this->audit('list_create', $list->label(), $id, 'list:' . $key);

        return redirect()->to("/admin/lists/{$key}")->with('success', $list->label() . ' item added.');
    }

    public function update(string $key, int $id): RedirectResponse
    {
        $list = $this->list($key);
        if ($list->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $values = $this->input($list);
        $errors = $list->validate($values);
        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        $list->update($id, $values);
        $this->audit('list_update', $list->label(), $id, 'list:' . $key);

        return redirect()->to("/admin/lists/{$key}")->with('success', $list->label() . ' item saved.');
    }

    public function delete(string $key, int $id): RedirectResponse
    {
        $this->requireRole('admin');

        $list = $this->list($key);
        if ($list->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // These tables have no deleted_at column, so this removes the row for
        // good. The hide action is the reversible way to take an item off the
        // site, and the form says so.
        $list->delete($id);
        $this->audit('list_delete', $list->label(), $id, 'list:' . $key);

        return redirect()->to("/admin/lists/{$key}")->with('success', 'Item deleted.');
    }

    public function toggle(string $key, int $id): RedirectResponse
    {
        $list = $this->list($key);
        $row  = $list->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $now = ! (bool) $row['is_active'];
        $list->setActive($id, $now);
        $this->audit($now ? 'list_show' : 'list_hide', $list->label(), $id, 'list:' . $key);

        return redirect()->to("/admin/lists/{$key}")
                         ->with('success', $now ? 'Item shown on the site.' : 'Item hidden from the site.');
    }

    public function move(string $key, int $id, string $direction): RedirectResponse
    {
        $list = $this->list($key);
        if ($list->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $list->move($id, $direction === 'up' ? 'up' : 'down');
        $this->audit('list_reorder', $list->label(), $id, 'list:' . $key);

        return redirect()->to("/admin/lists/{$key}");
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function list(string $key): ListItemModel
    {
        $list = ListItemModel::make($key);
        if ($list === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $list;
    }

    /** Read this list's fields off the submitted form. */
    private function input(ListItemModel $list): array
    {
        $post   = $this->request->getPost();
        $values = [];

        foreach ($list->fields() as $name => $f) {
            $raw = $post[$name] ?? '';

            if ($f['type'] === 'lines') {
                $raw    = str_replace(["\r\n", "\r"], "\n", (string) $raw);
                $values[$name] = array_values(array_filter(
                    array_map('trim', explode("\n", $raw)),
                    static fn ($v) => $v !== ''
                ));

                continue;
            }

            if ($f['type'] === 'numbers') {
                $values[$name] = array_values(array_filter(
                    array_map('trim', explode(',', (string) $raw)),
                    static fn ($v) => $v !== ''
                ));

                continue;
            }

            $values[$name] = is_string($raw) ? trim($raw) : $raw;
        }

        return $values;
    }
}
