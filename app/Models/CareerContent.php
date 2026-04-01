<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerContent extends Model
{
    public const TYPE_CAREER_GUIDANCE = 'career_guidance';

    public const TYPE_INTERVIEW_EXPERIENCE = 'interview_experience';

    public const TYPE_INTERVIEW_QA = 'interview_qa';

    protected $fillable = [
        'content_type',
        'category',
        'title',
        'subtitle',
        'body',
        'question',
        'answer',
        'rating_hint',
        'sort_order',
        'is_published',
        'published_at',
        'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'rating_hint' => 'decimal:1',
        ];
    }

    /** @return HasMany<CareerContentHelpfulVote, $this> */
    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(CareerContentHelpfulVote::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function helpfulUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'career_content_helpful_votes')
            ->withTimestamps();
    }
}
