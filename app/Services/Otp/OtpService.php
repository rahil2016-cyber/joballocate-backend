<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Cache;

final class OtpService
{
    /**
     * Canonical form for cache keys: email lowercased, or phone digits only.
     * Must match how users type the same contact in different formats (+91… vs 98…).
     */
    private function normalize(string $identifier): string
    {
        $trimmed = trim($identifier);

        if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            return strtolower($trimmed);
        }

        $digits = preg_replace('/\D/', '', $trimmed) ?? '';

        return $digits;
    }

    private function cacheKey(string $identifier): string
    {
        return 'otp:v1:'.sha1($this->normalize($identifier));
    }

    /**
     * Generate and store a numeric OTP. Later replace with SMS/email provider.
     */
    public function send(string $identifier): string
    {
        if (config('otp.use_fixed_code')) {
            $raw = (string) config('otp.fixed_code', '123456');
            $code = str_pad(substr(preg_replace('/\D/', '', $raw), 0, 6), 6, '0', STR_PAD_LEFT);
        } else {
            $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
        }

        Cache::put(
            $this->cacheKey($identifier),
            $code,
            now()->addSeconds((int) config('otp.ttl_seconds', 600))
        );

        return $code;
    }

    public function verify(string $identifier, string $code): bool
    {
        $key = $this->cacheKey($identifier);
        $expected = Cache::get($key);

        if ($expected === null || ! hash_equals((string) $expected, (string) $code)) {
            return false;
        }

        Cache::forget($key);

        return true;
    }
}
