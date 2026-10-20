<?php

namespace App\Controllers\Admin;

use App\Libraries\Notifier;
use App\Models\ApplicationModel;
use App\Models\EnquiryModel;
use App\Models\InboxModel;
use App\Models\InboxReplyModel;
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
        $counts = $this->enquiries->counts();
        $pager  = $this->listPage($counts[$status ?? 'all'] ?? 0);

        $rows = $this->enquiries->withStatus($status, $pager->perPage, $pager->offset);

        return $this->adminView('admin/inbox/enquiries', [
            'pageTitle' => 'Inbox — Enquiries',
            'tab'       => 'enquiries',
            'status'    => $status,
            'rows'      => $rows,
            'replied'   => $this->repliedFor('enquiry', $rows),
            'counts'    => $counts,
            'pager'     => $pager,
            // Editors never see applications, so no count for them either.
            'otherNew'  => $this->isAdmin() ? ($this->applications->counts()['new'] ?? 0) : 0,
        ]);
    }

    public function applications(): string
    {
        // Applications carry CVs — personal data — so only admins see them.
        $this->requireRole('admin');

        $status = $this->status();
        $counts = $this->applications->counts();
        $pager  = $this->listPage($counts[$status ?? 'all'] ?? 0);

        $rows = $this->applications->withStatus($status, $pager->perPage, $pager->offset);

        return $this->adminView('admin/inbox/applications', [
            'pageTitle' => 'Inbox — Applications',
            'tab'       => 'applications',
            'status'    => $status,
            'rows'      => $rows,
            'replied'   => $this->repliedFor('application', $rows),
            'counts'    => $counts,
            'pager'     => $pager,
            'otherNew'  => $this->enquiries->counts()['new'] ?? 0,
            'model'     => $this->applications,
        ]);
    }

    /** One message in full. Opening a new message marks it read. */
    public function show(string $type, int $id): string
    {
        [$model, $view] = $this->target($type);
        $this->requireAdminFor($type);

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

        $draft = Notifier::replyDraft($type, $row, (string) ($this->adminUser['name'] ?? ''));
        $data += [
            'replies'     => $this->safeReplies($type, $id),
            'draft'       => $draft,
            'mailReady'   => Notifier::status(),
            'senderEmail' => (string) ($this->adminUser['email'] ?? ''),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ];

        return $this->adminView('admin/inbox/show', $data);
    }

    public function status_(string $type, int $id, string $status): RedirectResponse
    {
        [$model] = $this->target($type);
        $this->requireAdminFor($type);

        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $model->setStatus($id, $status)) {
            return redirect()->back()->with('error', 'That is not a valid status.');
        }

        $this->audit('inbox_status', $type . ' → ' . $status, $id, 'inbox');

        return $this->toList($this->inboxPath($type))
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
        $this->requireRole('admin');

        $row = $this->applications->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $file = $this->applications->cvFile($row);
        if ($file === null) {
            return $this->toList('/admin/inbox/applications')
                             ->with('error', 'That CV file is no longer on the server.');
        }

        $this->audit('inbox_cv_download', $row['name'], $id, 'inbox');

        $name = $this->downloadName($row, $file);

        return $this->response->download($file, null)->setFileName($name);
    }

    /**
     * Email a reply to the person who wrote in, from the message page.
     * Editors may answer enquiries; applications stay admin-only.
     */
    public function reply(string $type, int $id): RedirectResponse
    {
        [$model] = $this->target($type);
        $this->requireAdminFor($type);

        $row = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $subject = trim((string) $this->request->getPost('subject'));
        $body    = trim((string) $this->request->getPost('body'));
        $errors  = [];
        if ($subject === '' || mb_strlen($subject) > 200) {
            $errors['subject'] = 'Give the reply a subject (up to 200 characters).';
        }
        if (mb_strlen($body) < 10) {
            $errors['body'] = 'Write the reply before sending it.';
        } elseif (mb_strlen($body) > 10000) {
            $errors['body'] = 'Keep the reply under 10,000 characters.';
        }
        if ($errors !== []) {
            return redirect()->to(admin_url('inbox/message/' . $type . '/' . $id) . '#reply')->withInput()
                ->with('errors', $errors)->with('error', 'The reply was not sent — please check it.');
        }

        $sender = ['name' => (string) ($this->adminUser['name'] ?? ''), 'email' => (string) ($this->adminUser['email'] ?? '')];
        $result = Notifier::reply(
            $type, $row, $subject, $body,
            $this->request->getPost('quote') !== null,
            $sender,
            $this->request->getPost('copy') !== null
        );

        if (! $result['ok']) {
            return redirect()->to(admin_url('inbox/message/' . $type . '/' . $id) . '#reply')->withInput()
                ->with('error', 'The reply was not sent. ' . $result['detail']);
        }

        // Kept only once it has really gone, so the history never shows a
        // reply the person did not receive.
        try {
            (new InboxReplyModel())->insert([
                'type'       => $type,
                'message_id' => $id,
                'user_id'    => $this->adminUser['id'] ?? null,
                'user_name'  => $sender['name'],
                'to_email'   => (string) $row['email'],
                'subject'    => $subject,
                'body'       => $body,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Reply sent but not recorded: ' . $e->getMessage());
        }
        if ($row['status'] === 'new') {
            $model->setStatus($id, 'read');
        }
        $this->audit('inbox_reply', $type . ' to ' . $row['name'], $id, 'inbox');

        return redirect()->to(admin_url('inbox/message/' . $type . '/' . $id) . '#replies')->with('success', $result['detail']);
    }

    /**
     * Remove a message for good — with its CV, for an application. Admins
     * only. People can ask for their details to be deleted, and nothing here
     * needs keeping once it has been dealt with.
     */
    public function delete(string $type, int $id): RedirectResponse
    {
        $this->requireRole('admin');
        [$model] = $this->target($type);

        $row = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($type === 'application' && ($file = $this->applications->cvFile($row)) !== null) {
            @unlink($file);
        }
        $model->delete($id);
        try {
            (new InboxReplyModel())->forgetMessage($type, $id);
        } catch (\Throwable $e) {
            log_message('error', 'Reply history not removed: ' . $e->getMessage());
        }
        $this->audit('inbox_delete', $type . ' from ' . $row['name'], $id, 'inbox');

        return $this->toList($this->inboxPath($type))
            ->with('success', ($type === 'application' ? 'Application and CV' : 'Message') . ' deleted.');
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /** The reply history, or none if the table is unreadable. */
    private function safeReplies(string $type, int $id): array
    {
        try {
            return (new InboxReplyModel())->forMessage($type, $id);
        } catch (\Throwable $e) {
            log_message('error', 'Reply history unavailable: ' . $e->getMessage());

            return [];
        }
    }

    /** message id => when last replied, for the list's "Replied" label. */
    private function repliedFor(string $type, array $rows): array
    {
        try {
            return (new InboxReplyModel())->lastReplied($type, array_map('intval', array_column($rows, 'id')));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Applications and CVs are admin-only; enquiries are open to editors. */
    private function requireAdminFor(string $type): void
    {
        if ($type === 'application') {
            $this->requireRole('admin');
        }
    }

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

    /** The list a message of this type belongs to. */
    private function inboxPath(string $type): string
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
