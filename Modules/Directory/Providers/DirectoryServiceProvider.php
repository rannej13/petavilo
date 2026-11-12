<?php

declare(strict_types=1);

namespace Modules\Directory\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Directory\Models\Business;

class DirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('Directory', 'config/directory.php'), 'directory');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Directory', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Directory', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Directory', 'resources/views'), 'directory');
        $this->loadTranslationsFrom(module_path('Directory', 'lang'), 'directory');

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('directory.index')],
            ...collect(array_keys(Business::types()))->map(static fn (string $type): array => ['url' => route('directory.type', $type)])->all(),
            ...Business::query()->published()->get(['type', 'slug', 'updated_at'])->map(static fn (Business $business): array => [
                'url' => route('directory.show', [$business->getAttribute('type'), $business->getAttribute('slug')]),
                'updated' => $business->getAttribute('updated_at')?->toAtomString(),
            ])->all(),
        ]);
    }
}
