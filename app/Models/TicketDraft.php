<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a user's in-progress ticket draft saved server-side.
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $current_step
 * @property array       $data          JSON-cast form snapshot (excludes file objects).
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TicketDraft extends Model
{
    protected $fillable = ['user_id', 'current_step', 'data'];

    protected $casts = [
        'data'         => 'array',
        'current_step' => 'integer',
    ];

    /**
     * Files temporarily stored on disk for this draft.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TicketDraftAttachment>
     */
    public function attachments()
    {
        return $this->hasMany(TicketDraftAttachment::class);
    }

    /**
     * The user who owns this draft.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, TicketDraft>
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
