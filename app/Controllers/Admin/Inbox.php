<?php namespace App\Controllers\Admin;

/** Handles Enquiries (contact) and Career Applications tabs */
class Inbox extends AdminController
{
    public function index(): string
    {
        return $this->enquiries();
    }

    public function enquiries(): string
    {
        $db   = db_connect();
        $rows = [];
        try { $rows = $db->table('enquiries')->orderBy('created_at','DESC')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/inbox/enquiries', [
            'pageTitle' => 'Inbox — Enquiries',
            'rows'      => $rows,
            'tab'       => 'enquiries',
        ]);
    }

    public function applications(): string
    {
        $db   = db_connect();
        $rows = [];
        try { $rows = $db->table('career_applications')->orderBy('created_at','DESC')->get()->getResultArray(); } catch (\Throwable) {}
        return $this->adminView('admin/inbox/applications', [
            'pageTitle'  => 'Inbox — Applications',
            'rows'       => $rows,
            'tab'        => 'applications',
        ]);
    }

    public function markRead(int $id, string $type = 'enquiry'): \CodeIgniter\HTTP\RedirectResponse
    {
        $table = $type === 'application' ? 'career_applications' : 'enquiries';
        db_connect()->table($table)->where('id', $id)->update(['status' => 'read', 'read_at' => date('Y-m-d H:i:s')]);
        $back = $type === 'application' ? '/admin/inbox/applications' : '/admin/inbox';
        return redirect()->to($back)->with('success', 'Marked as read.');
    }
}
