<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeekerFeedback extends Model
{
    protected $table = 'seeker_feedback';

    protected $fillable = [
        'user_id',
        'rating',
        'message',
        'admin_reply',
        'admin_reply_user_id',
        'admin_replied_at',
        'admin_quality_rating',
    ];

    protected function casts(): array
    {
        return [
            'admin_replied_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function adminReplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_reply_user_id');
    }
}
