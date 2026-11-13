<?php

declare(strict_types=1);

namespace Modules\PetTools\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\PetTools\Console\SendRemindersCommand;

class PetToolsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('PetTools', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('PetTools', 'routes/web.php'));
        $this->loadViewsFrom(module_path('PetTools', 'resources/views'), 'pettools');
        $this->loadTranslationsFrom(module_path('PetTools', 'lang'), 'pettools');

        if ($this->app->runningInConsole()) {
            $this->commands([SendRemindersCommand::class]);
        }

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command('pets:reminders')->dailyAt('08:15');
        });

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('pettools.index')],
            ['url' => route('pettools.poison')],
            ['url' => route('pettools.food')],
            ['url' => route('pettools.passport.about')],
        ]);
    }
}
