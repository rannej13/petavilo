<?php

declare(strict_types=1);

namespace Modules\CatMarket\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CatMarket\Console\ImagesCommand;
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
            $this->commands([InstallCommand::class, ImagesCommand::class, \Modules\CatMarket\Console\ListingRemindersCommand::class, \Modules\CatMarket\Console\AdminDigestCommand::class]);
        }

        Listing::observe(AnimalListingObserver::class);

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Registered::class, \Modules\CatMarket\Listeners\SendWelcomeEmail::class);

        $this->callAfterResolving(\Illuminate\Console\Scheduling\Schedule::class, static function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
            $schedule->command('catmarket:listing-reminders')->dailyAt('09:05')->withoutOverlapping();
            $schedule->command('catmarket:admin-digest')->dailyAt('08:35')->withoutOverlapping();
        });

        $this->app->booted(static fn () => MonetisationConfig::apply());
        \Illuminate\Support\Facades\Queue::before(static function (): void {
            MonetisationConfig::apply();
        });

        $this->app['router']->pushMiddlewareToGroup('web', RememberRegion::class);
    }
}
