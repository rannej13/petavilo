<?php

declare(strict_types=1);

namespace Modules\Ads\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('Ads', 'config/ads.php'), 'ads');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Ads', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Ads', 'resources/views'), 'ads');
        $this->loadTranslationsFrom(module_path('Ads', 'lang'), 'ads');
        Blade::anonymousComponentPath(module_path('Ads', 'resources/views/components'), 'ads');
    }
}
