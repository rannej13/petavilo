<?php

declare(strict_types=1);

namespace Modules\Breeder\Providers;

use Illuminate\Support\ServiceProvider;

class BreederServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Breeder', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Breeder', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Breeder', 'resources/views'), 'breeder');

        if (class_exists(\App\Support\SitemapRegistry::class)) {
            \App\Support\SitemapRegistry::register(static fn (): array => [['url' => route('breeder.landing')]]);
        }
    }
}
