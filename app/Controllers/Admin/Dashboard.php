<?php

namespace App\Controllers\Admin;

class Dashboard extends AdminController
{
    public function index(): string
    {
        $db = db_connect();

        $data = [
            'pageTitle'        => 'Dashboard',
            'unreadEnquiries'  => 0,
            'unreadApplications' => 0,
            'machineCount'     => 0,
            'newsCount'        => 0,
        ];

        try {
            $data['unreadEnquiries']    = $db->table('enquiries')->where('status', 'new')->countAllResults();
            $data['unreadApplications'] = $db->table('career_applications')->where('status', 'new')->countAllResults();
            $data['machineCount']       = $db->table('machines')->where('deleted_at IS NULL', null, false)->countAllResults();
            $data['newsCount']          = $db->table('news_articles')->where('deleted_at IS NULL', null, false)->countAllResults();
        } catch (\Throwable) {
            // DB tables might be empty; not a fatal error
        }

        return $this->adminView('admin/dashboard', $data);
    }
}
