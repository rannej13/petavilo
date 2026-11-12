<?php

declare(strict_types=1);

namespace Modules\Directory\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Directory\Console\DedupeBusinessesCommand;
use Modules\Directory\Console\ImportPlacesCommand;
use Modules\Directory\Console\SendClaimInvitesCommand;
use Modules\Directory\Models\Business;

class DirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('Directory', 'config/directory.php'), 'directory');

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command('directory:import-places')->name('directory:import-places')->hourly()->withoutOverlapping();
            $schedule->command('directory:invite')->name('directory:invite')->weekdays()->at('10:07')->withoutOverlapping()
                ->when(static fn (): bool => (bool) config('directory.outreach.enabled', false));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Directory', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Directory', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Directory', 'resources/views'), 'directory');
        $this->loadTranslationsFrom(module_path('Directory', 'lang'), 'directory');

        if ($this->app->runningInConsole()) {
            $this->commands([ImportPlacesCommand::class, DedupeBusinessesCommand::class, SendClaimInvitesCommand::class]);
        }

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('directory.index')],
            ...collect(array_keys(Business::types()))->map(static fn (string $type): array => ['url' => route('directory.type', $type)])->all(),
            ...Business::query()->published()
                ->where(static fn ($query) => $query->whereNull('source')->orWhereNotNull('owner_user_id'))
                ->latest('updated_at')->limit(20000)
                ->get(['type', 'slug', 'updated_at'])->map(static fn (Business $business): array => [
                'url' => route('directory.show', [$business->getAttribute('type'), $business->getAttribute('slug')]),
                'updated' => $business->getAttribute('updated_at')?->toAtomString(),
            ])->all(),
        ]);
    }
}
