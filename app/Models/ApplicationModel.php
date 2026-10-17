<?php

namespace App\Models;

/**
 * Career applications from the careers form.
 *
 * `cv_path` is relative to writable/uploads/, deliberately outside the web
 * root: CVs are personal data and must not be fetchable by URL. The admin
 * downloads them through Admin\Inbox::cv().
 */
class ApplicationModel extends InboxModel
{
    protected $table      = 'career_applications';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 'email', 'phone', 'area', 'experience', 'message',
        'cv_path', 'status',
    ];

    /** Absolute path of a stored CV, or null when the file is gone. */
    public function cvFile(array $row): ?string
    {
        $relative = trim((string) ($row['cv_path'] ?? ''));
        if ($relative === '') {
            return null;
        }

        // Never let a stored value climb out of the uploads directory.
        $base = realpath(WRITEPATH . 'uploads');
        $file = realpath(WRITEPATH . 'uploads/' . $relative);

        if ($base === false || $file === false || ! str_starts_with($file, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return is_file($file) ? $file : null;
    }
}
