<?php

declare(strict_types=1);

namespace Modules\Tv\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Tv\Console\PickWinnerCommand;

class TvServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Tv', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Tv', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Tv', 'resources/views'), 'tv');

        if ($this->app->runningInConsole()) {
            $this->commands([PickWinnerCommand::class]);
        }

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command('tv:pick-winner')->weeklyOn(1, '09:10')->withoutOverlapping();
        });

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('tv.index')],
            ['url' => route('tv.potw')],
        ]);
    }
}
