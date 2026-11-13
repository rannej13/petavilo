<?php

declare(strict_types=1);

namespace Modules\Rescue\Listeners;

use Laravel\Cashier\Events\WebhookReceived;
use Modules\Rescue\Models\Donation;
use Modules\Rescue\Support\DonationGateway;
use Stripe\Checkout\Session;

final class HandleDonationWebhook
{
    public function handle(WebhookReceived $event): void
    {
        if (! in_array($event->payload['type'] ?? null, ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.expired'], true)) {
            return;
        }

        $object = $event->payload['data']['object'] ?? null;

        if (! is_array($object) || ($object['metadata']['purpose'] ?? null) !== 'rescue_donation') {
            return;
        }

        $donation = Donation::query()->find((int) ($object['metadata']['donation_id'] ?? 0));

        if ($donation instanceof Donation && $donation->getAttribute('stripe_session_id') === ($object['id'] ?? null)) {
            DonationGateway::apply($donation, Session::constructFrom($object));
        }
    }
}
