<?php namespace App\Controllers\Admin;

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
