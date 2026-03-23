<?php

namespace App\Support;

/**
 * @deprecated Catalog is stored in `seeker_packages` and exposed via API.
 * Kept only for reference; do not use in new code.
 */
final class JobSeekerPackages
{
    /**
     * @return list<array{key: string, title: string, price_inr: int, applications: int, duration_days: int}>
     */
    public static function catalog(): array
    {
        return [
            [
                'key' => 'basic',
                'title' => 'Basic',
                'price_inr' => 399,
                'applications' => 5,
                'duration_days' => 25,
            ],
            [
                'key' => 'standard',
                'title' => 'Standard',
                'price_inr' => 999,
                'applications' => 10,
                'duration_days' => 50,
            ],
            [
                'key' => 'premium',
                'title' => 'Premium',
                'price_inr' => 1499,
                'applications' => 16,
                'duration_days' => 65,
            ],
        ];
    }

    /**
     * @return array{applications: int, duration_days: int, price_inr: int, title: string}
     */
    public static function definition(string $key): array
    {
        foreach (self::catalog() as $row) {
            if ($row['key'] === $key) {
                return [
                    'applications' => $row['applications'],
                    'duration_days' => $row['duration_days'],
                    'price_inr' => $row['price_inr'],
                    'title' => $row['title'],
                ];
            }
        }

        throw new \InvalidArgumentException('Unknown package key: '.$key);
    }
}
