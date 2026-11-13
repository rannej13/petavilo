<?php

declare(strict_types=1);

namespace Modules\Rescue\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;
use Modules\Rescue\Listeners\HandleDonationWebhook;
use Modules\Rescue\Console\ReconcileDonationsCommand;
use Modules\Rescue\Models\RescueOrganisation;

class RescueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('Rescue', 'config/rescue.php'), 'rescue');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ReconcileDonationsCommand::class]);
        }

        Event::listen(WebhookReceived::class, [HandleDonationWebhook::class, 'handle']);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command('donations:reconcile')->everyTenMinutes();
        });

        $this->loadMigrationsFrom(module_path('Rescue', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Rescue', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Rescue', 'resources/views'), 'rescue');
        $this->loadTranslationsFrom(module_path('Rescue', 'lang'), 'rescue');

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('rescue.adopt')],
            ['url' => route('rescue.rehome')],
            ['url' => route('rescue.lost-found')],
            ['url' => route('rescue.found-animal')],
            ['url' => route('rescue.foster')],
            ['url' => route('rescue.rescues')],
            ['url' => route('rescue.welfare')],
            ...RescueOrganisation::query()->verified()->get(['slug'])->map(static fn (RescueOrganisation $organisation): array => [
                'url' => route('rescue.show', $organisation->getAttribute('slug')),
            ])->all(),
        ]);
    }
}
