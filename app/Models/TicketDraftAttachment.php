<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single file persisted to disk that belongs to a TicketDraft.
 * Rows are deleted (cascade) when the parent draft is deleted.
 *
 * @property int $id
 * @property int $ticket_draft_id
 * @property string $file_name Original file name as uploaded by the user.
 * @property string $file_path Storage-relative path under draft-attachments/{draft_id}/.
 * @property string $file_type MIME type.
 * @property int $file_size Size in bytes.
 */
class TicketDraftAttachment extends Model
{
    protected $fillable = [
        'ticket_draft_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    /**
     * The draft this attachment belongs to.
     *
     * @return BelongsTo<TicketDraft, TicketDraftAttachment>
     */
    public function draft()
    {
        return $this->belongsTo(TicketDraft::class, 'ticket_draft_id');
    }
}
