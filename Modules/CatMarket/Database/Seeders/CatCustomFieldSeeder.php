<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\CatMarket\Support\PetCatalog;
use Modules\Category\Models\Category;
use Modules\Listing\Models\ListingCustomField;

class CatCustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        foreach (array_keys(PetCatalog::leaves()) as $leafSlug) {
            $leaf = Category::query()->where('slug', $leafSlug)->first();

            if (! $leaf instanceof Category) {
                continue;
            }

            foreach (array_values(PetCatalog::fieldsFor($leafSlug)) as $index => $field) {
                ListingCustomField::upsertSeeded($leaf, array_merge($field, [
                    'name' => Str::slug($leafSlug.'_'.$field['name'], '_'),
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ]));
                $count++;
            }
        }

        $this->command?->info("Seeded {$count} pet listing fields.");
    }
}
