<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'referral_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'user_id');
    }

    public function jobSeekerProfile(): HasOne
    {
        return $this->hasOne(JobSeekerProfile::class, 'user_id');
    }

    /** Snapshots of each plan activation (persists across devices; current credits live on job_seeker_profiles). */
    public function seekerPackagePurchases(): HasMany
    {
        return $this->hasMany(SeekerPackagePurchase::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function resumeDrafts(): HasMany
    {
        return $this->hasMany(ResumeDraft::class);
    }

    /** All registered device tokens for push notifications (multi-device support). */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(UserDeviceToken::class);
    }

    /** In-app notification history. */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role->value;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin);
    }
}
