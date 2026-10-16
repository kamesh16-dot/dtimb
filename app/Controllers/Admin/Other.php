<?php namespace App\Controllers\Admin;

/** Catch-all stub controllers for Pages, Lists, Media, SEO, Redirects, Settings, Users */

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

class ListsHub extends AdminController
{
    public function index(): string
    {
        return $this->adminView('admin/lists/index', ['pageTitle' => 'Lists Hub']);
    }
}

class Media extends AdminController
{
    public function index(): string
    {
        $rows = [];
        try { $rows = db_connect()->table('media')->orderBy('created_at','DESC')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/media/index', ['pageTitle' => 'Media Library', 'rows' => $rows]);
    }

    public function upload(): \CodeIgniter\HTTP\RedirectResponse
    {
        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return $this->backWithError('No valid file uploaded.');
        }
        $allowed = ['jpg','jpeg','png','gif','webp','pdf','svg'];
        if (! in_array(strtolower($file->getExtension()), $allowed)) {
            return $this->backWithError('File type not allowed.');
        }
        $name = $file->getRandomName();
        $file->move(FCPATH . 'uploads/', $name);
        db_connect()->table('media')->insert([
            'filename'   => $name,
            'original'   => $file->getClientName(),
            'mime_type'  => $file->getClientMimeType(),
            'size'       => $file->getSize(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('media_upload', $name);
        return redirect()->to('/admin/media')->with('success', 'File uploaded.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        $row = db_connect()->table('media')->where('id', $id)->get()->getRowArray();
        if ($row && file_exists(FCPATH . 'uploads/' . $row['filename'])) {
            unlink(FCPATH . 'uploads/' . $row['filename']);
        }
        db_connect()->table('media')->where('id', $id)->delete();
        $this->audit('media_delete', '', $id);
        return redirect()->to('/admin/media')->with('success', 'File deleted.');
    }
}

class Seo extends AdminController
{
    public function index(): string
    {
        $rows = [];
        try { $rows = db_connect()->table('pages')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/seo/index', ['pageTitle' => 'SEO', 'rows' => $rows]);
    }
}

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
        db_connect()->table('redirects')->insert(['from_url' => $post['from_url'] ?? '', 'to_url' => $post['to_url'] ?? '', 'code' => (int)($post['code'] ?? 301), 'created_at' => date('Y-m-d H:i:s')]);
        return redirect()->to('/admin/redirects')->with('success', 'Redirect added.');
    }
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        db_connect()->table('redirects')->where('id', $id)->delete();
        return redirect()->to('/admin/redirects')->with('success', 'Redirect deleted.');
    }
}

class Settings extends AdminController
{
    public function index(): string
    {
        $this->requireRole('admin');
        $settings = [];
        try {
            $rows = db_connect()->table('settings')->get()->getResultArray();
            foreach ($rows as $r) { $settings[$r['key']] = $r['value']; }
        } catch (\Throwable) {}
        return $this->adminView('admin/settings/index', ['pageTitle' => 'Settings', 'settings' => $settings]);
    }
    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        $db   = db_connect();
        $post = $this->request->getPost();
        foreach ($post as $key => $value) {
            if (str_starts_with($key, 'setting_')) {
                $k = substr($key, 8);
                $exists = $db->table('settings')->where('key', $k)->countAllResults();
                if ($exists) { $db->table('settings')->where('key', $k)->update(['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]); }
                else { $db->table('settings')->insert(['key' => $k, 'value' => $value, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]); }
            }
        }
        $this->audit('settings_update');
        return redirect()->to('/admin/settings')->with('success', 'Settings saved.');
    }
}

class Users extends AdminController
{
    public function index(): string
    {
        $this->requireRole('admin');
        $rows = db_connect()->table('users')->where('deleted_at IS NULL', null, false)->get()->getResultArray();
        return $this->adminView('admin/users/index', ['pageTitle' => 'Users', 'rows' => $rows]);
    }
    public function create(): string
    {
        $this->requireRole('admin');
        return $this->adminView('admin/users/form', ['pageTitle' => 'Add User', 'row' => []]);
    }
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        $post = $this->request->getPost();
        db_connect()->table('users')->insert([
            'name'          => $post['name']  ?? '',
            'email'         => $post['email'] ?? '',
            'password_hash' => password_hash($post['password'] ?? 'changeme', PASSWORD_BCRYPT),
            'role'          => $post['role']  ?? 'editor',
            'is_active'     => 1,
            'force_password_change' => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->audit('user_create', $post['email'] ?? '');
        return redirect()->to('/admin/users')->with('success', 'User created.');
    }
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('admin');
        if ($id === ($this->adminUser['id'] ?? 0)) {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete yourself.');
        }
        db_connect()->table('users')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->audit('user_delete', '', $id);
        return redirect()->to('/admin/users')->with('success', 'User removed.');
    }
}
