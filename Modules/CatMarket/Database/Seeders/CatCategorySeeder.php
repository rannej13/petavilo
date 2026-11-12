<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CatMarket\Support\CatCatalog;
use Modules\Category\Models\Category;

class CatCategorySeeder extends Seeder
{
    public function run(): void
    {
        $catSlugs = [];

        foreach (CatCatalog::categories() as $index => $data) {
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
            $catSlugs[] = $data['slug'];

            foreach ($data['children'] as $childIndex => $childName) {
                $childSlug = CatCatalog::childSlug($data['slug'], $childName);
                $catSlugs[] = $childSlug;

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

        Category::query()->whereNotIn('slug', $catSlugs)->update(['is_active' => false]);
    }
}
