<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudiencePromoRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'audience',
        'code_used',
        'audience_promo_code_id',
        'referrer_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(AudiencePromoCode::class, 'audience_promo_code_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }
}
