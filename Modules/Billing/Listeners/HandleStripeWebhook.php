<?php

declare(strict_types=1);

namespace Modules\Billing\Listeners;

use App\Support\BusinessDirectory;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Events\WebhookReceived;
use Modules\Billing\Support\MembershipCatalog;
use Modules\Billing\Support\PaymentGateway;
use Modules\User\App\Models\User;
use Stripe\Checkout\Session;

final class HandleStripeWebhook
{
    public function handleReceived(WebhookReceived $event): void
    {
        if (($event->payload['type'] ?? null) !== 'checkout.session.completed') {
            return;
        }

        $object = $event->payload['data']['object'] ?? null;

        if (! is_array($object)) {
            return;
        }

        PaymentGateway::fulfilPromotion(Session::constructFrom($object));
    }

    public function handleHandled(WebhookHandled $event): void
    {
        $type = (string) ($event->payload['type'] ?? '');

        if (! str_starts_with($type, 'customer.subscription.')) {
            return;
        }

        $customerId = (string) ($event->payload['data']['object']['customer'] ?? '');
        $user = $customerId !== '' ? Cashier::findBillable($customerId) : null;

        if (! $user instanceof User) {
            return;
        }

        BusinessDirectory::syncTierForOwner((int) $user->getKey(), MembershipCatalog::directoryTier($user));
    }
}
