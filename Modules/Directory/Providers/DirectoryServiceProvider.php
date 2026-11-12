<?php

declare(strict_types=1);

namespace Modules\Directory\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Directory\Console\ImportPlacesCommand;
use Modules\Directory\Models\Business;

class DirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('Directory', 'config/directory.php'), 'directory');

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command('directory:import-places')->name('directory:import-places')->hourly()->withoutOverlapping();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Directory', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Directory', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Directory', 'resources/views'), 'directory');
        $this->loadTranslationsFrom(module_path('Directory', 'lang'), 'directory');

        if ($this->app->runningInConsole()) {
            $this->commands([ImportPlacesCommand::class]);
        }

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
