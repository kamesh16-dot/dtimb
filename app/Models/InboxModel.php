<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Shared behaviour for the two inbox tables: website enquiries and career
 * applications. Both carry a `status` of new, read or archived.
 */
abstract class InboxModel extends Model
{
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    public const STATUSES = ['new', 'read', 'archived'];

    /** Human labels for the filter tabs. */
    public const LABELS = [
        'new'      => 'New',
        'read'     => 'Read',
        'archived' => 'Archived',
    ];

    /** The columns this inbox accepts, for checking against what the forms write. */
    public function allowedFieldList(): array
    {
        return $this->allowedFields;
    }

    /**
     * Messages for one status, newest first. An unknown or empty status
     * returns everything, which is what the "All" tab asks for.
     */
    public function withStatus(?string $status): array
    {
        $builder = $this->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');

        if ($status !== null && in_array($status, self::STATUSES, true)) {
            $builder->where('status', $status);
        }

        return $builder->findAll();
    }

    /** How many messages sit in each status, plus the total. */
    public function counts(): array
    {
        $counts = ['all' => 0];
        foreach (self::STATUSES as $status) {
            $counts[$status] = 0;
        }

        $rows = $this->builder()->select('status, COUNT(*) AS n')->groupBy('status')->get()->getResultArray();
        foreach ($rows as $r) {
            $counts[$r['status']] = (int) $r['n'];
            $counts['all'] += (int) $r['n'];
        }

        return $counts;
    }

    public function setStatus(int $id, string $status): bool
    {
        if (! in_array($status, self::STATUSES, true)) {
            return false;
        }

        return (bool) $this->update($id, ['status' => $status]);
    }

    /** Mark as read on first open, leaving read and archived alone. */
    public function markReadOnOpen(array $row): void
    {
        if (($row['status'] ?? '') === 'new') {
            $this->setStatus((int) $row['id'], 'read');
        }
    }
}
