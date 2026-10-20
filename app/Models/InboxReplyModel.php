<?php

namespace App\Models;

use CodeIgniter\Model;

/** Replies sent from the Inbox. See the CreateInboxReplies migration. */
class InboxReplyModel extends Model
{
    protected $table         = 'inbox_replies';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = ['type', 'message_id', 'user_id', 'user_name', 'to_email', 'subject', 'body'];

    /** Every reply to one message, oldest first. */
    public function forMessage(string $type, int $id): array
    {
        return $this->where('type', $type)->where('message_id', $id)->orderBy('id', 'ASC')->findAll();
    }

    /**
     * When each of these messages was last replied to.
     *
     * @param list<int> $ids
     * @return array<int,string> message id => date of the latest reply
     */
    public function lastReplied(string $type, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = $this->builder()->select('message_id, MAX(created_at) AS at')
            ->where('type', $type)->whereIn('message_id', $ids)
            ->groupBy('message_id')->get()->getResultArray();

        return array_column($rows, 'at', 'message_id');
    }

    /** Remove the history with the message it belongs to. */
    public function forgetMessage(string $type, int $id): void
    {
        $this->where('type', $type)->where('message_id', $id)->delete();
    }
}
