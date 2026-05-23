<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudiencePromoCode extends Model
{
    public const AUDIENCE_JOB_SEEKER = 'job_seeker';

    public const AUDIENCE_COMPANY = 'company';

    protected $fillable = [
        'code',
        'audience',
        'label',
        'benefit_description',
        'is_active',
        'max_redemptions',
        'redemptions_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_redemptions' => 'integer',
            'redemptions_count' => 'integer',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(AudiencePromoRedemption::class);
    }
}
