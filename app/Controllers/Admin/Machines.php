<?php namespace App\Controllers\Admin;

class Machines extends AdminController
{
    public function index(): string
    {
        $db   = db_connect();
        $rows = [];
        try {
            $rows = $db->table('machines')
                       ->where('deleted_at IS NULL', null, false)
                       ->orderBy('sort_order', 'ASC')
                       ->get()->getResultArray();
        } catch (\Throwable) {}

        return $this->adminView('admin/machines/index', [
            'pageTitle' => 'Machines',
            'rows'      => $rows,
        ]);
    }

    public function create(): string
    {
        return $this->adminView('admin/machines/form', [
            'pageTitle' => 'Add Machine',
            'row'       => [],
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->_formData();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('machines')->insert($data);
        $this->audit('machine_create', $data['name'] ?? '');
        return redirect()->to('/admin/machines')->with('success', 'Machine created.');
    }

    public function edit(int $id): string
    {
        $row = db_connect()->table('machines')->where('id', $id)->get()->getRowArray();
        if (! $row) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return $this->adminView('admin/machines/form', [
            'pageTitle' => 'Edit Machine',
            'row'       => $row,
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->_formData();
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('machines')->where('id', $id)->update($data);
        $this->audit('machine_update', $data['name'] ?? '', $id);
        return redirect()->to('/admin/machines')->with('success', 'Machine updated.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        db_connect()->table('machines')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->audit('machine_delete', '', $id);
        return redirect()->to('/admin/machines')->with('success', 'Machine deleted.');
    }

    private function _formData(): array
    {
        $post = $this->request->getPost();
        return [
            'name'        => $post['name']        ?? '',
            'slug'        => $post['slug']        ?? '',
            'category'    => $post['category']    ?? '',
            'summary'     => $post['summary']     ?? '',
            'description' => $post['description'] ?? '',
            'is_featured' => isset($post['is_featured']) ? 1 : 0,
            'sort_order'  => (int)($post['sort_order'] ?? 0),
        ];
    }
}
