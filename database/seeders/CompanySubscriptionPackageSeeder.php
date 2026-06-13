<?php

namespace Database\Seeders;

use App\Models\CompanySubscriptionPackage;
use Illuminate\Database\Seeder;

class CompanySubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        CompanySubscriptionPackage::query()->updateOrCreate(
            ['title' => 'Corporate Package'],
            [
                'monthly_price_inr' => 499,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
