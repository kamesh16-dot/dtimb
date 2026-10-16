<?php namespace App\Controllers\Admin;

class Seo extends AdminController
{
    public function index(): string
    {
        $rows = [];
        try { $rows = db_connect()->table('pages')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/seo/index', ['pageTitle' => 'SEO', 'rows' => $rows]);
    }
}
