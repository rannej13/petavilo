<?php

declare(strict_types=1);

namespace Modules\Rescue\Support;

use Laravel\Cashier\Cashier;
use Modules\Rescue\Models\Donation;
use Modules\Rescue\Models\RescueOrganisation;
use Stripe\Checkout\Session;
use Throwable;

final class DonationGateway
{
    public static function isConfigured(): bool
    {
        return filled(config('cashier.secret')) && filled(config('cashier.key'));
    }

    public static function onboardingUrl(RescueOrganisation $organisation, string $email): string
    {
        $stripe = Cashier::stripe();
        $accountId = (string) $organisation->getAttribute('stripe_account_id');

        if ($accountId === '') {
            $account = $stripe->accounts->create([
                'type' => 'standard',
                'country' => $organisation->getAttribute('country') === 'US' ? 'US' : 'GB',
                'email' => $email,
                'business_profile' => [
                    'name' => (string) $organisation->getAttribute('name'),
                    'url' => $organisation->getAttribute('website') ?: route('rescue.show', $organisation->getAttribute('slug')),
                ],
                'metadata' => ['rescue_id' => (string) $organisation->getKey()],
            ]);
            $accountId = (string) $account->id;
            $organisation->forceFill(['stripe_account_id' => $accountId])->save();
        }

        return (string) $stripe->accountLinks->create([
            'account' => $accountId,
            'refresh_url' => route('rescue.donations.connect.refresh'),
            'return_url' => route('rescue.donations.connect.return'),
            'type' => 'account_onboarding',
        ])->url;
    }

    public static function refreshAccount(RescueOrganisation $organisation): void
    {
        $accountId = (string) $organisation->getAttribute('stripe_account_id');

        if ($accountId === '') {
            return;
        }

        try {
            $account = Cashier::stripe()->accounts->retrieve($accountId);
        } catch (Throwable) {
            return;
        }

        $organisation->forceFill([
            'payouts_ready' => (bool) $account->charges_enabled && (bool) $account->payouts_enabled,
        ])->save();
    }

    public static function checkout(Donation $donation, RescueOrganisation $organisation): string
    {
        $currency = strtolower((string) $donation->getAttribute('currency'));
        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'submit_type' => 'donate',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => (int) round($donation->total() * 100),
                    'product_data' => ['name' => 'Donation to '.(string) $organisation->getAttribute('name')],
                ],
            ]],
            'customer_email' => $donation->user?->email,
            'payment_intent_data' => [
                'description' => 'Donation via '.(string) config('catmarket.site_name', 'Petavilo'),
                'metadata' => ['purpose' => 'rescue_donation', 'donation_id' => (string) $donation->getKey()],
            ],
            'metadata' => ['purpose' => 'rescue_donation', 'donation_id' => (string) $donation->getKey()],
            'success_url' => route('rescue.donations.thanks', $donation->getKey()).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('rescue.show', $organisation->getAttribute('slug')),
        ], ['stripe_account' => (string) $organisation->getAttribute('stripe_account_id')]);

        $donation->forceFill([
            'stripe_session_id' => (string) $session->id,
            'stripe_account_id' => (string) $organisation->getAttribute('stripe_account_id'),
        ])->save();

        return (string) $session->url;
    }

    public static function sync(Donation $donation): void
    {
        $sessionId = (string) $donation->getAttribute('stripe_session_id');
        $accountId = (string) $donation->getAttribute('stripe_account_id');

        if ($sessionId === '' || $accountId === '') {
            return;
        }

        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [], ['stripe_account' => $accountId]);
        } catch (Throwable) {
            return;
        }

        self::apply($donation, $session);
    }

    public static function apply(Donation $donation, Session $session): void
    {
        if (($session->payment_status ?? null) === 'paid' && (int) ($session->amount_total ?? 0) >= (int) round($donation->total() * 100)) {
            $donation->markPaid($session->customer_details?->email ?? null, is_string($session->payment_intent ?? null) ? $session->payment_intent : null);

            return;
        }

        if (($session->status ?? null) === 'expired') {
            $donation->forceFill(['status' => Donation::STATUS_FAILED])->save();
        }
    }
}
