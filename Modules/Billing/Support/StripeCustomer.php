<?php

declare(strict_types=1);

namespace Modules\Billing\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\PermissionException;
use Throwable;

/**
 * Customers and subscriptions created while the site used Stripe test keys do
 * not exist in the live account. Before any live call, check the stored
 * customer and forget it (and its test subscriptions) if Stripe doesn't know it.
 */
final class StripeCustomer
{
    public static function heal(object $user): void
    {
        if (! method_exists($user, 'hasStripeId') || ! $user->hasStripeId()) {
            return;
        }

        try {
            $customer = $user->asStripeCustomer();

            if (! empty($customer->deleted)) {
                self::forget($user);
            }
        } catch (InvalidRequestException|PermissionException $exception) {
            if (self::isMissing($exception)) {
                self::forget($user);
            }
        } catch (Throwable) {
            return;
        }
    }

    public static function isMissing(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return ($exception instanceof InvalidRequestException && $exception->getStripeCode() === 'resource_missing')
            || str_contains($message, 'no such')
            || str_contains($message, 'similar object exists in test mode')
            || str_contains($message, 'similar object exists in live mode');
    }

    private static function forget(object $user): void
    {
        $userId = $user->getKey();

        if (Schema::hasTable('subscriptions')) {
            $ids = DB::table('subscriptions')->where('user_id', $userId)->pluck('id');

            if ($ids->isNotEmpty() && Schema::hasTable('subscription_items')) {
                DB::table('subscription_items')->whereIn('subscription_id', $ids)->delete();
            }

            DB::table('subscriptions')->where('user_id', $userId)->delete();
        }

        $user->forceFill(['stripe_id' => null, 'pm_type' => null, 'pm_last_four' => null])->saveQuietly();
        $user->unsetRelation('subscriptions');
    }
}
