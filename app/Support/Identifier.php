<?php

namespace App\Support;

final class Identifier
{
    public static function cleanPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 13 && str_starts_with($digits, '091')) {
            $digits = substr($digits, 3);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        return $digits;
    }

    public static function parse(string $raw): array
    {
        $raw = trim($raw);

        if (filter_var($raw, FILTER_VALIDATE_EMAIL)) {
            return [
                'type' => 'email',
                'email' => strtolower($raw),
                'phone' => null,
            ];
        }

        $digits = self::cleanPhone($raw);

        return [
            'type' => 'phone',
            'email' => null,
            'phone' => $digits !== '' ? $digits : null,
        ];
    }

    public static function syntheticEmailFromPhone(string $digits): string
    {
        return 'phone_'.$digits.'@internal.joballocate';
    }

    public static function resolveLoginEmail(array $parts): string
    {
        if ($parts['email'] !== null) {
            return $parts['email'];
        }

        if ($parts['phone'] !== null) {
            return self::syntheticEmailFromPhone($parts['phone']);
        }

        throw new \InvalidArgumentException('Identifier must contain a valid email or phone.');
    }

    /** True for placeholder emails created for phone-only registration. */
    public static function isSyntheticEmail(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        return str_ends_with(strtolower($email), '@internal.joballocate');
    }
}
