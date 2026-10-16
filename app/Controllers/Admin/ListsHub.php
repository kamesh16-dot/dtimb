<?php namespace App\Controllers\Admin;

class ListsHub extends AdminController
{
    public function index(): string
    {
        return $this->adminView('admin/lists/index', ['pageTitle' => 'Lists Hub']);
    }
}
