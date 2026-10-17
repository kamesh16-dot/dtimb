<?php

namespace App\Models;

/**
 * Website enquiries from the contact form.
 *
 * Rows are written by App\Controllers\Forms on submission; nothing in the
 * admin creates one. `notes` is the only field an admin edits.
 */
class EnquiryModel extends InboxModel
{
    protected $table      = 'enquiries';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 'email', 'phone', 'company', 'interest', 'message',
        'ip', 'user_agent', 'status', 'notes',
    ];
}
