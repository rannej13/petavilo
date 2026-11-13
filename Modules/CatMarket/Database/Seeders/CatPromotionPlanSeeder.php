<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Promotion\Models\PromotionPlan;

class CatPromotionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $slugs = [];

        foreach ($this->plans() as $plan) {
            PromotionPlan::query()->updateOrCreate(['slug' => $plan['slug']], array_merge($plan, ['currency' => 'GBP', 'is_active' => true]));
            $slugs[] = $plan['slug'];
        }

        PromotionPlan::query()->whereNotIn('slug', $slugs)->update(['is_active' => false]);
    }

    private function plans(): array
    {
        return [
            [
                'slug' => 'bump',
                'price_usd' => 0.99,
                'name' => 'Bump to top',
                'description' => 'Move your advert back to the top of its category.',
                'price' => 0.99,
                'duration_days' => 1,
                'grants_featured' => false,
                'grants_urgent' => false,
                'bump_count' => 1,
                'benefits' => ['Back to the top of results'],
                'sort_order' => 1,
            ],
            [
                'slug' => 'urgent-7',
                'price_usd' => 2.49,
                'name' => 'Urgent',
                'description' => 'Show people this needs to go quickly.',
                'price' => 1.99,
                'duration_days' => 7,
                'grants_featured' => false,
                'grants_urgent' => true,
                'bump_count' => 1,
                'benefits' => ['Urgent badge for 7 days', 'One bump to the top'],
                'sort_order' => 2,
            ],
            [
                'slug' => 'featured-7',
                'price_usd' => 5.99,
                'name' => 'Featured 7 days',
                'description' => 'Top of the category with a highlighted card for a week.',
                'price' => 4.99,
                'duration_days' => 7,
                'grants_featured' => true,
                'grants_urgent' => false,
                'bump_count' => 1,
                'benefits' => ['Featured badge', 'Top placement in category results', 'Homepage featured rotation'],
                'sort_order' => 3,
            ],
            [
                'slug' => 'featured-30',
                'price_usd' => 17.99,
                'name' => 'Featured 30 days',
                'description' => 'A full month at the top of the category.',
                'price' => 14.99,
                'duration_days' => 30,
                'grants_featured' => true,
                'grants_urgent' => false,
                'bump_count' => 1,
                'benefits' => ['Featured badge for 30 days', 'Top placement in category results', 'Homepage featured rotation'],
                'sort_order' => 4,
            ],
        ];
    }
}
