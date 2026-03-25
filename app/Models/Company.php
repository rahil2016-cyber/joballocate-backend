<?php

namespace App\Models;

use App\Enums\CompanyVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'industry',
        'industry_type',
        'website',
        'description',
        'gst_number',
        'location',
        'state',
        'district',
        'city',
        'established_year',
        'company_bio',
        'what_we_do',
        'team_members',
        'logo_url',
        'verification_status',
        'verified_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'verification_status' => CompanyVerificationStatus::class,
            'team_members' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === CompanyVerificationStatus::Verified;
    }
}
