<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\Directory\Database\Seeders\DirectorySeeder;
use Modules\Guide\Database\Seeders\GuideSeeder;

class CatMarketSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('settings')) {
            Artisan::call('migrate', ['--path' => 'database/settings', '--force' => true]);
        }

        $this->call([
            AdminUserSeeder::class,
            SiteSettingsSeeder::class,
            UkLocationSeeder::class,
            CatCategorySeeder::class,
            CatCustomFieldSeeder::class,
            CatPromotionPlanSeeder::class,
            CatPageSeeder::class,
            GuideSeeder::class,
            DirectorySeeder::class,
            ExampleListingSeeder::class,
            \Modules\Community\Database\Seeders\CommunitySeeder::class,
        ]);
    }
}
