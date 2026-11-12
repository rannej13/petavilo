<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\CatMarket\Support\CatCatalog;
use Modules\Category\Models\Category;
use Modules\Listing\Models\ListingCustomField;

class CatCustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        foreach (CatCatalog::categories() as $root) {
            foreach ($root['children'] as $childName) {
                $leafSlug = CatCatalog::childSlug($root['slug'], $childName);
                $leaf = Category::query()->where('slug', $leafSlug)->first();

                if (! $leaf instanceof Category) {
                    continue;
                }

                foreach (array_values(CatCatalog::fieldsFor($root['slug'], $leafSlug)) as $index => $field) {
                    ListingCustomField::upsertSeeded($leaf, array_merge($field, [
                        'name' => Str::slug($leafSlug.'_'.$field['name'], '_'),
                        'is_active' => true,
                        'sort_order' => ($index + 1) * 10,
                    ]));
                    $count++;
                }
            }
        }

        $this->command?->info("Seeded {$count} cat listing fields.");
    }
}
