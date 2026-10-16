<?php namespace App\Controllers\Admin;

class Redirects extends AdminController
{
    public function index(): string
    {
        $rows = [];
        try { $rows = db_connect()->table('redirects')->orderBy('from_url')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/redirects/index', ['pageTitle' => 'Redirects', 'rows' => $rows]);
    }
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost();
        db_connect()->table('redirects')->insert([
            'from_url'   => $post['from_url'] ?? '',
            'to_url'     => $post['to_url']   ?? '',
            'code'       => (int)($post['code'] ?? 301),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/redirects')->with('success', 'Redirect added.');
    }
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        db_connect()->table('redirects')->where('id', $id)->delete();
        return redirect()->to('/admin/redirects')->with('success', 'Redirect deleted.');
    }
}
