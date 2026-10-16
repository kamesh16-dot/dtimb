<?php namespace App\Controllers\Admin;

class Pages extends AdminController
{
    public function index(): string
    {
        $rows = [];
        try { $rows = db_connect()->table('pages')->where('deleted_at IS NULL', null, false)->orderBy('slug')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/pages/index', ['pageTitle' => 'Pages', 'rows' => $rows]);
    }
    public function edit(string $slug): string
    {
        $row = db_connect()->table('pages')->where('slug', $slug)->get()->getRowArray() ?? [];
        return $this->adminView('admin/pages/form', ['pageTitle' => 'Edit Page', 'row' => $row, 'slug' => $slug]);
    }
    public function update(string $slug): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost();
        $db   = db_connect();
        $exists = $db->table('pages')->where('slug', $slug)->countAllResults();
        $data = ['slug' => $slug, 'meta_title' => $post['meta_title'] ?? '', 'meta_description' => $post['meta_description'] ?? '', 'content' => $post['content'] ?? '', 'updated_at' => date('Y-m-d H:i:s')];
        if ($exists) { $db->table('pages')->where('slug', $slug)->update($data); }
        else { $data['created_at'] = date('Y-m-d H:i:s'); $db->table('pages')->insert($data); }
        $this->audit('page_update', $slug);
        return redirect()->to('/admin/pages')->with('success', 'Page saved.');
    }
}
