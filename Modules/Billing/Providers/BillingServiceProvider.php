<?php

declare(strict_types=1);

namespace Modules\Billing\Providers;

use App\Support\BusinessDirectory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Events\WebhookReceived;
use Modules\Billing\Listeners\HandleStripeWebhook;
use Modules\Billing\Support\MembershipCatalog;
use Modules\User\App\Models\User;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(module_path('Billing', 'config/billing.php'), 'billing');

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->call(static function (): void {
                foreach (BusinessDirectory::ownerIds() as $ownerId) {
                    $owner = User::query()->find($ownerId);

                    if ($owner instanceof User) {
                        BusinessDirectory::syncTierForOwner((int) $ownerId, MembershipCatalog::directoryTier($owner));
                    }
                }
            })->name('billing:sync-directory-tiers')->dailyAt('03:15')->withoutOverlapping();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Billing', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Billing', 'resources/views'), 'billing');
        $this->loadTranslationsFrom(module_path('Billing', 'lang'), 'billing');

        Event::listen(WebhookReceived::class, [HandleStripeWebhook::class, 'handleReceived']);
        Event::listen(WebhookHandled::class, [HandleStripeWebhook::class, 'handleHandled']);
    }
}
