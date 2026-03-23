<?php

namespace App\Support;

final class Identifier
{
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

        $digits = preg_replace('/\D/', '', $raw) ?? '';

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
}
