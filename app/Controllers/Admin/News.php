<?php namespace App\Controllers\Admin;

class News extends AdminController
{
    public function index(): string
    {
        $db   = db_connect();
        $rows = [];
        try {
            $rows = $db->table('news_articles')
                       ->where('deleted_at IS NULL', null, false)
                       ->orderBy('published_at', 'DESC')
                       ->get()->getResultArray();
        } catch (\Throwable) {}

        return $this->adminView('admin/news/index', [
            'pageTitle' => 'News Articles',
            'rows'      => $rows,
        ]);
    }

    public function create(): string
    {
        return $this->adminView('admin/news/form', [
            'pageTitle' => 'New Article',
            'row'       => [],
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->_formData();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('news_articles')->insert($data);
        $this->audit('news_create', $data['title'] ?? '');
        return redirect()->to('/admin/news')->with('success', 'Article created.');
    }

    public function edit(int $id): string
    {
        $row = db_connect()->table('news_articles')->where('id', $id)->get()->getRowArray();
        if (! $row) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return $this->adminView('admin/news/form', [
            'pageTitle' => 'Edit Article',
            'row'       => $row,
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->_formData();
        $data['updated_at'] = date('Y-m-d H:i:s');
        db_connect()->table('news_articles')->where('id', $id)->update($data);
        $this->audit('news_update', $data['title'] ?? '', $id);
        return redirect()->to('/admin/news')->with('success', 'Article updated.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        db_connect()->table('news_articles')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->audit('news_delete', '', $id);
        return redirect()->to('/admin/news')->with('success', 'Article deleted.');
    }

    private function _formData(): array
    {
        $post = $this->request->getPost();
        return [
            'title'        => $post['title']        ?? '',
            'slug'         => $post['slug']         ?? '',
            'excerpt'      => $post['excerpt']      ?? '',
            'body'         => $post['body']         ?? '',
            'published_at' => $post['published_at'] ?? date('Y-m-d'),
            'is_published' => isset($post['is_published']) ? 1 : 0,
        ];
    }
}
