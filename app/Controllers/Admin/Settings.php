<?php

namespace App\Controllers\Admin;

use App\Libraries\Notifier;
use App\Models\SiteSettingModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Site identity: the name, tagline, contact email and links the layout
 * renders on every page.
 *
 * Addresses shown on the contact page are not here — they are locations, and
 * live in the Lists Hub. Mail server details are not here either: they are
 * credentials and belong in the .env file, not the database.
 */
class Settings extends AdminController
{
    public function index(): string
    {
        $this->requireRole('admin');

        return $this->adminView('admin/settings/index', [
            'pageTitle' => 'Settings',
            'settings'  => SiteSettingModel::all(),
            'fields'    => SiteSettingModel::FIELDS,
            'mail'      => $this->mailStatus(),
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(): RedirectResponse
    {
        $this->requireRole('admin');

        $post   = $this->request->getPost();
        $errors = SiteSettingModel::problems($post);

        if ($errors !== []) {
            return $this->backWithErrors($errors);
        }

        SiteSettingModel::save($post);
        $this->audit('settings_update', 'Site settings', null, 'settings');

        return redirect()->to('/admin/settings')->with('success', 'Settings saved.');
    }

    /** Send a sample email to the recipients, so the setup can be checked. */
    public function testEmail(): RedirectResponse
    {
        $this->requireRole('admin');

        $result = Notifier::test((string) ($this->adminUser['name'] ?? 'an admin'));
        $this->audit('settings_test_email', $result['ok'] ? 'sent' : 'failed', null, 'settings');

        return redirect()->to('/admin/settings#email')
            ->with($result['ok'] ? 'success' : 'error', ($result['ok'] ? 'Test email sent. ' : 'Test email not sent. ') . $result['detail']);
    }

    /**
     * Whether outgoing mail is ready. Read-only: the server and password are
     * credentials, so they stay in .env — not in the database, and not
     * editable through the browser.
     */
    private function mailStatus(): array
    {
        $email  = config('Email');
        $status = Notifier::status();

        return [
            'configured' => $status['ok'],
            'problem'    => $status['problem'],
            'host'       => trim((string) $email->SMTPHost),
            'port'       => (int) $email->SMTPPort,
            'user'       => trim((string) $email->SMTPUser),
            'from'       => trim((string) $email->fromEmail) ?: trim((string) $email->SMTPUser),
            'to'         => implode(', ', Notifier::recipients()),
        ];
    }
}
