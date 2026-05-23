<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AudiencePromoCode;
use App\Models\AudiencePromoRedemption;
use App\Models\CompanyCoupon;
use App\Models\User;
use Illuminate\Support\Str;

final class ReferEarnService
{
    public function __construct(
        private readonly PlatformSettingService $platformSettings
    ) {}

    public function isEnabledFor(string $audience): bool
    {
        $s = $this->platformSettings->referEarnSettings();

        return $audience === AudiencePromoCode::AUDIENCE_COMPANY
            ? (bool) $s['company_enabled']
            : (bool) $s['job_seeker_enabled'];
    }

    /**
     * @return array{
     *   enabled: bool,
     *   benefits_text: string,
     *   app_download_url: ?string,
     *   play_store_coming_soon: bool,
     *   my_referral_code: ?string,
     *   share_message: string
     * }
     */
    public function publicPayload(string $audience, ?User $user = null): array
    {
        $settings = $this->platformSettings->referEarnSettings();
        $enabled = $audience === AudiencePromoCode::AUDIENCE_COMPANY
            ? (bool) $settings['company_enabled']
            : (bool) $settings['job_seeker_enabled'];

        $benefits = $audience === AudiencePromoCode::AUDIENCE_COMPANY
            ? (string) ($settings['company_benefits_text'] ?? '')
            : (string) ($settings['job_seeker_benefits_text'] ?? '');

        $url = trim((string) ($settings['app_download_url'] ?? ''));
        $hasUrl = $url !== '';

        $myCode = null;
        if ($user) {
            $myCode = $this->ensureUserReferralCode($user);
        }

        $share = $this->buildShareMessage($myCode, $benefits, $hasUrl, $url);

        return [
            'enabled' => $enabled,
            'benefits_text' => $benefits,
            'app_download_url' => $hasUrl ? $url : null,
            'play_store_coming_soon' => ! $hasUrl,
            'my_referral_code' => $myCode,
            'share_message' => $share,
        ];
    }

    /**
     * @return array{valid: bool, message: string, code_type?: string, benefit_description?: string}
     */
    public function validateForRegistration(string $rawCode, string $audience): array
    {
        $code = $this->normalizeCode($rawCode);
        if ($code === '') {
            return ['valid' => false, 'message' => 'Enter a referral or promo code.'];
        }

        if (! in_array($audience, [AudiencePromoCode::AUDIENCE_JOB_SEEKER, AudiencePromoCode::AUDIENCE_COMPANY], true)) {
            return ['valid' => false, 'message' => 'Invalid audience.'];
        }

        // Company subscription coupons are NOT valid at registration.
        $companyCoupon = CompanyCoupon::query()
            ->whereRaw('lower(code) = ?', [$code])
            ->where('is_active', true)
            ->first();

        if ($companyCoupon) {
            if ($audience === AudiencePromoCode::AUDIENCE_JOB_SEEKER) {
                return [
                    'valid' => false,
                    'message' => 'This code is for employers only. Job seekers cannot use company subscription coupons.',
                ];
            }

            return [
                'valid' => false,
                'message' => 'This is a subscription coupon. Use it under Subscriptions after your company is verified — not during sign-up.',
                'code_type' => 'company_subscription',
            ];
        }

        // Another user's personal referral code.
        $referrer = User::query()
            ->whereRaw('lower(referral_code) = ?', [$code])
            ->first();

        if ($referrer) {
            $expectedRole = $audience === AudiencePromoCode::AUDIENCE_COMPANY
                ? UserRole::Company
                : UserRole::JobSeeker;

            if ($referrer->role !== $expectedRole) {
                return [
                    'valid' => false,
                    'message' => $audience === AudiencePromoCode::AUDIENCE_COMPANY
                        ? 'This referral code belongs to a job seeker. Employers must use an employer referral code.'
                        : 'This referral code belongs to an employer. Job seekers must use a job seeker referral code.',
                ];
            }

            $settings = $this->platformSettings->referEarnSettings();
            $benefit = $audience === AudiencePromoCode::AUDIENCE_COMPANY
                ? (string) ($settings['company_benefits_text'] ?? '')
                : (string) ($settings['job_seeker_benefits_text'] ?? '');

            return [
                'valid' => true,
                'message' => 'Referral code accepted.',
                'code_type' => 'user_referral',
                'benefit_description' => $benefit,
                'referrer_user_id' => $referrer->id,
            ];
        }

        // Admin promo code for audience.
        $promo = AudiencePromoCode::query()
            ->where('audience', $audience)
            ->whereRaw('lower(code) = ?', [$code])
            ->first();

        if ($promo) {
            if (! $promo->is_active) {
                return ['valid' => false, 'message' => 'This promo code is no longer active.'];
            }
            if ($promo->max_redemptions !== null && $promo->redemptions_count >= $promo->max_redemptions) {
                return ['valid' => false, 'message' => 'This promo code has reached its usage limit.'];
            }

            return [
                'valid' => true,
                'message' => 'Promo code accepted.',
                'code_type' => 'audience_promo',
                'benefit_description' => (string) ($promo->benefit_description ?? ''),
                'audience_promo_code_id' => $promo->id,
            ];
        }

        // Wrong-audience promo exists?
        $other = AudiencePromoCode::query()
            ->where('audience', '!=', $audience)
            ->whereRaw('lower(code) = ?', [$code])
            ->where('is_active', true)
            ->exists();

        if ($other) {
            return [
                'valid' => false,
                'message' => $audience === AudiencePromoCode::AUDIENCE_COMPANY
                    ? 'This code is for job seekers only.'
                    : 'This code is for employers only.',
            ];
        }

        return ['valid' => false, 'message' => 'Invalid referral or promo code.'];
    }

    public function redeemOnRegistration(User $user, string $rawCode, string $audience): void
    {
        if (AudiencePromoRedemption::query()->where('user_id', $user->id)->exists()) {
            return;
        }

        $check = $this->validateForRegistration($rawCode, $audience);
        if (! ($check['valid'] ?? false)) {
            return;
        }

        $code = $this->normalizeCode($rawCode);

        AudiencePromoRedemption::query()->create([
            'user_id' => $user->id,
            'audience' => $audience,
            'code_used' => $code,
            'audience_promo_code_id' => $check['audience_promo_code_id'] ?? null,
            'referrer_user_id' => $check['referrer_user_id'] ?? null,
        ]);

        if (! empty($check['audience_promo_code_id'])) {
            AudiencePromoCode::query()
                ->where('id', $check['audience_promo_code_id'])
                ->increment('redemptions_count');
        }

        $this->ensureUserReferralCode($user);
    }

    public function ensureUserReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return (string) $user->referral_code;
        }

        do {
            $code = 'JA'.strtoupper(Str::random(6));
        } while (User::query()->where('referral_code', $code)->exists());

        $user->forceFill(['referral_code' => $code])->save();

        return $code;
    }

    private function normalizeCode(string $raw): string
    {
        return strtoupper(trim($raw));
    }

    private function buildShareMessage(?string $code, string $benefits, bool $hasUrl, string $url): string
    {
        $parts = ['Join JobAllocate!'];
        if ($code) {
            $parts[] = "Use my code: {$code}";
        }
        if ($benefits !== '') {
            $parts[] = $benefits;
        }
        if ($hasUrl) {
            $parts[] = "Download: {$url}";
        } else {
            $parts[] = 'App on Play Store — coming soon. Use the referral code when you register.';
        }

        return implode("\n", $parts);
    }
}
