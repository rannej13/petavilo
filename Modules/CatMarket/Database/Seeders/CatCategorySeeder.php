<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\CatMarket\Support\PetCatalog;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;

class CatCategorySeeder extends Seeder
{
    public function run(): void
    {
        $slugs = [];

        foreach (PetCatalog::categories() as $index => $data) {
            $parent = Category::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                    'parent_id' => null,
                    'level' => 0,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
            $slugs[] = $data['slug'];

            foreach ($data['children'] as $childIndex => [$childName]) {
                $childSlug = PetCatalog::childSlug($data['slug'], $childName);
                $slugs[] = $childSlug;

                Category::query()->updateOrCreate(
                    ['slug' => $childSlug],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->getKey(),
                        'level' => 1,
                        'sort_order' => $childIndex,
                        'is_active' => true,
                    ],
                );
            }
        }

        $this->moveLegacyListings();

        Category::query()->whereNotIn('slug', $slugs)->update(['is_active' => false]);
    }

    private function moveLegacyListings(): void
    {
        $map = PetCatalog::legacySlugMap();
        $ids = Category::query()->whereIn('slug', [...array_keys($map), ...array_values($map)])->pluck('id', 'slug');

        foreach ($map as $old => $new) {
            $oldId = $ids[$old] ?? null;
            $newId = $ids[$new] ?? null;

            if ($oldId === null || $newId === null || $oldId === $newId) {
                continue;
            }

            Listing::query()->withTrashed()->where('category_id', $oldId)->get()->each(function (Listing $listing) use ($old, $new, $newId): void {
                $fields = [];

                foreach ((array) ($listing->getAttribute('custom_fields') ?? []) as $key => $value) {
                    $prefix = Str::slug($old, '_').'_';
                    $fields[str_starts_with((string) $key, $prefix) ? Str::slug($new, '_').'_'.substr((string) $key, strlen($prefix)) : $key] = $value;
                }

                $listing->forceFill(['category_id' => $newId, 'custom_fields' => $fields])->saveQuietly();
            });
        }
    }
}
