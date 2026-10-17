<?php

namespace App\Controllers\Admin;

use App\Models\ApplicationModel;
use App\Models\EnquiryModel;
use App\Models\InboxModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Inbox: website enquiries and career applications.
 *
 * Messages arrive from App\Controllers\Forms. Nothing is created here — an
 * admin reads, annotates, archives and downloads CVs.
 */
class Inbox extends AdminController
{
    protected EnquiryModel $enquiries;
    protected ApplicationModel $applications;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->enquiries    = new EnquiryModel();
        $this->applications = new ApplicationModel();
    }

    public function index(): string
    {
        return $this->enquiries();
    }

    public function enquiries(): string
    {
        $status = $this->status();

        return $this->adminView('admin/inbox/enquiries', [
            'pageTitle' => 'Inbox — Enquiries',
            'tab'       => 'enquiries',
            'status'    => $status,
            'rows'      => $this->enquiries->withStatus($status),
            'counts'    => $this->enquiries->counts(),
            'otherNew'  => $this->applications->counts()['new'] ?? 0,
        ]);
    }

    public function applications(): string
    {
        $status = $this->status();

        return $this->adminView('admin/inbox/applications', [
            'pageTitle' => 'Inbox — Applications',
            'tab'       => 'applications',
            'status'    => $status,
            'rows'      => $this->applications->withStatus($status),
            'counts'    => $this->applications->counts(),
            'otherNew'  => $this->enquiries->counts()['new'] ?? 0,
            'model'     => $this->applications,
        ]);
    }

    /** One message in full. Opening a new message marks it read. */
    public function show(string $type, int $id): string
    {
        [$model, $view] = $this->target($type);

        $row = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->markReadOnOpen($row);
        $row['status'] = $row['status'] === 'new' ? 'read' : $row['status'];

        $data = [
            'pageTitle' => $row['name'],
            'tab'       => $view,
            'type'      => $type,
            'row'       => $row,
        ];

        if ($type === 'application') {
            $data['hasCv'] = $this->applications->cvFile($row) !== null;
        }

        return $this->adminView('admin/inbox/show', $data);
    }

    public function status_(string $type, int $id, string $status): RedirectResponse
    {
        [$model] = $this->target($type);

        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $model->setStatus($id, $status)) {
            return redirect()->back()->with('error', 'That is not a valid status.');
        }

        $this->audit('inbox_status', $type . ' → ' . $status, $id, 'inbox');

        return redirect()->to($this->listUrl($type))
                         ->with('success', 'Moved to ' . (InboxModel::LABELS[$status] ?? $status) . '.');
    }

    /** Internal note on an enquiry. */
    public function note(int $id): RedirectResponse
    {
        if ($this->enquiries->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->enquiries->update($id, ['notes' => trim((string) $this->request->getPost('notes'))]);
        $this->audit('inbox_note', 'enquiry', $id, 'inbox');

        return redirect()->to('/admin/inbox/message/enquiry/' . $id)->with('success', 'Note saved.');
    }

    /**
     * Stream a CV. These live outside the web root on purpose, so this is the
     * only way to reach one, and it needs an admin session.
     */
    public function cv(int $id)
    {
        $row = $this->applications->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $file = $this->applications->cvFile($row);
        if ($file === null) {
            return redirect()->to('/admin/inbox/applications')
                             ->with('error', 'That CV file is no longer on the server.');
        }

        $this->audit('inbox_cv_download', $row['name'], $id, 'inbox');

        $name = $this->downloadName($row, $file);

        return $this->response->download($file, null)->setFileName($name);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /** The requested filter, or null for "all". */
    private function status(): ?string
    {
        $status = (string) ($this->request->getGet('status') ?? 'new');

        return in_array($status, InboxModel::STATUSES, true) ? $status : null;
    }

    /** @return array{0:InboxModel,1:string} */
    private function target(string $type): array
    {
        return match ($type) {
            'enquiry'     => [$this->enquiries, 'enquiries'],
            'application' => [$this->applications, 'applications'],
            default       => throw PageNotFoundException::forPageNotFound(),
        };
    }

    private function listUrl(string $type): string
    {
        return $type === 'application' ? '/admin/inbox/applications' : '/admin/inbox';
    }

    /** A readable file name for the download, keeping the real extension. */
    private function downloadName(array $row, string $file): string
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $row['name']) ?? 'applicant');
        $ext  = pathinfo($file, PATHINFO_EXTENSION);

        return trim($slug, '-') . '-cv' . ($ext !== '' ? '.' . $ext : '');
    }
}
