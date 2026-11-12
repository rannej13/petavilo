<?php

declare(strict_types=1);

namespace Modules\CatMarket\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CatMarket\Console\InstallCommand;
use Modules\CatMarket\Http\Middleware\RememberRegion;
use Modules\CatMarket\Observers\AnimalListingObserver;
use Modules\CatMarket\Support\MonetisationConfig;
use Modules\Listing\Models\Listing;

class CatMarketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('CatMarket', 'config/catmarket.php'), 'catmarket');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('CatMarket', 'Database/migrations'));
        $this->loadViewsFrom(module_path('CatMarket', 'resources/views'), 'catmarket');
        $this->loadTranslationsFrom(module_path('CatMarket', 'lang'), 'catmarket');

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }

        Listing::observe(AnimalListingObserver::class);

        $this->app->booted(static fn () => MonetisationConfig::apply());

        $this->app['router']->pushMiddlewareToGroup('web', RememberRegion::class);
    }
}
