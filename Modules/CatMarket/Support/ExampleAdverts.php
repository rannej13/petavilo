<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Modules\CatMarket\Database\Seeders\ExampleListingSeeder;
use Modules\Listing\Models\Listing;
use Throwable;

final class ExampleAdverts
{
    public static function sync(bool $show): void
    {
        try {
            config(['catmarket.show_examples' => $show]);

            if (! $show) {
                Listing::query()->where('is_example', true)->get()->each->delete();
            } else {
                $trashed = Listing::query()->onlyTrashed()->where('is_example', true)->get();
                $trashed->each->restore();

                if (! Listing::query()->where('is_example', true)->exists()) {
                    app(ExampleListingSeeder::class)->run();
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
