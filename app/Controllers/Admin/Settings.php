<?php

namespace App\Controllers\Admin;

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

    /**
     * Whether outgoing mail is configured. Read-only: the host and password
     * are credentials, so they stay in .env where they are not in the
     * database and not editable through the browser.
     */
    private function mailStatus(): array
    {
        $email = config('Email');
        $host  = trim((string) ($email->SMTPHost ?? ''));

        return [
            'configured' => $host !== '',
            'host'       => $host,
            'protocol'   => (string) ($email->protocol ?? 'mail'),
            'from'       => (string) ($email->fromEmail ?? ''),
        ];
    }
}
