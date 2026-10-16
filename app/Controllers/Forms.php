<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Contact / enquiry and career-application forms.
 *
 * Every submission is saved to writable/forms/<kind>-YYYY-MM.jsonl (CVs go to
 * writable/uploads/careers/) and an email is attempted to Config\Site::$email.
 * Email only works once app/Config/Email.php (SMTP) is configured - until then
 * the saved files are the record.
 */
class Forms extends BaseController
{
    public function contact(): RedirectResponse
    {
        $back = base_url('contact') . '#enquiry';

        return $this->handle('contact', $back, [
            'name'    => 'required|min_length[2]|max_length[100]',
            'email'   => 'required|valid_email|max_length[150]',
            'phone'   => 'permit_empty|min_length[6]|max_length[30]|regex_match[/^[0-9+()\\s-]+$/]',
            'company' => 'permit_empty|max_length[120]',
            'interest'=> 'permit_empty|max_length[80]',
            'message' => 'required|min_length[10]|max_length[3000]',
        ]);
    }

    public function career(): RedirectResponse
    {
        $back = base_url('careers') . '#apply';

        return $this->handle('career', $back, [
            'name'       => 'required|min_length[2]|max_length[100]',
            'email'      => 'required|valid_email|max_length[150]',
            'phone'      => 'required|min_length[6]|max_length[30]|regex_match[/^[0-9+()\\s-]+$/]',
            'area'       => 'required|max_length[80]',
            'experience' => 'permit_empty|numeric|greater_than_equal_to[0]|less_than[60]',
            'message'    => 'permit_empty|max_length[2000]',
            'resume'     => 'uploaded[resume]|max_size[resume,5120]|ext_in[resume,pdf,doc,docx]'
                          . '|mime_in[resume,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]',
        ], 'resume');
    }

    private function handle(string $kind, string $back, array $rules, ?string $fileField = null): RedirectResponse
    {
        // Hidden "website" field: humans leave it empty, bots fill it in. Pretend success.
        if ((string) $this->request->getPost('website') !== '') {
            return redirect()->to($back)->with('form_ok', $kind);
        }

        if (! $this->validate($rules)) {
            return redirect()->to($back)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        foreach (array_keys($rules) as $field) {
            if ($field !== $fileField) {
                $data[$field] = trim((string) $this->request->getPost($field));
            }
        }

        $attachment = null;
        if ($fileField) {
            $file = $this->request->getFile($fileField);
            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $dir = WRITEPATH . 'uploads/careers';
                if (! is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $name = $file->getRandomName();
                $file->move($dir, $name);
                $attachment = $dir . '/' . $name;
                $data['resume_file'] = $name;
                $data['resume_original'] = $file->getClientName();
            }
        }

        $this->store($kind, $data);
        $this->mail($kind, $data, $attachment);

        return redirect()->to($back)->with('form_ok', $kind);
    }

    private function store(string $kind, array $data): void
    {
        $dir = WRITEPATH . 'forms';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $row = ['at' => date('c'), 'ip' => $this->request->getIPAddress()] + $data;
        file_put_contents($dir . '/' . $kind . '-' . date('Y-m') . '.jsonl', json_encode($row, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    }

    private function mail(string $kind, array $data, ?string $attachment): void
    {
        try {
            $site  = config('Site');
            $email = service('email');
            $from  = config('Email')->fromEmail ?: $site->email;

            $email->setFrom($from, $site->name . ' website');
            $email->setTo($site->email);
            $email->setReplyTo($data['email'], $data['name']);
            $email->setSubject(($kind === 'career' ? 'Career application' : 'Website enquiry') . ' - ' . $data['name']);

            $lines = [];
            foreach ($data as $k => $v) {
                $lines[] = ucfirst(str_replace('_', ' ', $k)) . ': ' . $v;
            }
            $email->setMessage(implode("\n", $lines));
            if ($attachment) {
                $email->attach($attachment);
            }
            if (! $email->send(false)) {
                log_message('warning', 'Form email not sent (configure app/Config/Email.php). Saved in writable/forms/.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Form email failed: ' . $e->getMessage());
        }
    }
}
