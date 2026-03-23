<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerAd extends Model
{
    protected $fillable = [
        'title',
        'content',
        'target_url',
        'background_color',
        'image_path',
        'status',
        'starts_at',
        'expires_at',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Absolute URL for clients (app, admin). Served via /media/banner-ads/{file}
     * so images work even when the web server blocks /storage or the symlink is missing.
     */
    public function publicImageUrl(): ?string
    {
        if ($this->image_path === null || $this->image_path === '') {
            return null;
        }

        $name = basename((string) $this->image_path);
        if ($name === '' || $name === '.' || $name === '..') {
            return null;
        }

        return url('/media/banner-ads/'.$name);
    }
}

