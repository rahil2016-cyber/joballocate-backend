<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerContentHelpfulVote extends Model
{
    protected $table = 'career_content_helpful_votes';

    protected $fillable = [
        'user_id',
        'career_content_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<CareerContent, $this> */
    public function careerContent(): BelongsTo
    {
        return $this->belongsTo(CareerContent::class);
    }
}
