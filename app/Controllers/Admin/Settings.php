<?php namespace App\Controllers\Admin;

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
                $k      = substr($key, 8);
                $exists = $db->table('settings')->where('key', $k)->countAllResults();
                if ($exists) {
                    $db->table('settings')->where('key', $k)->update(['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
                } else {
                    $db->table('settings')->insert(['key' => $k, 'value' => $value, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }
        }
        $this->audit('settings_update');
        return redirect()->to('/admin/settings')->with('success', 'Settings saved.');
    }
}
