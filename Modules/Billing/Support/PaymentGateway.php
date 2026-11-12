<?php

declare(strict_types=1);

namespace Modules\Billing\Support;

use App\Support\ListingDirectory;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Modules\Promotion\Models\PromotionOrder;
use Modules\Promotion\Models\PromotionPlan;
use Modules\User\App\Models\User;
use Stripe\Checkout\Session;
use Throwable;

final class PaymentGateway
{
    public const PURPOSE_PROMOTION = 'promotion';

    public static function isConfigured(): bool
    {
        return filled(config('cashier.secret')) && filled(config('cashier.key'));
    }

    public static function promotionCheckout(User $user, PromotionPlan $plan, int $listingId): Checkout
    {
        $listingCurrency = (string) (ListingDirectory::find($listingId)['currency'] ?? $plan->getAttribute('currency'));
        $currency = $plan->currencyFor($listingCurrency);
        $amount = (int) round($plan->priceFor($listingCurrency) * 100);
        $metadata = [
            'purpose' => self::PURPOSE_PROMOTION,
            'user_id' => (string) $user->getKey(),
            'listing_id' => (string) $listingId,
            'plan_id' => (string) $plan->getKey(),
            'currency' => $currency,
        ];

        return $user->checkout([[
            'price_data' => [
                'currency' => strtolower($currency),
                'product_data' => ['name' => (string) $plan->getAttribute('name')],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]], [
            'mode' => 'payment',
            'success_url' => route('billing.promotions.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('panel.promotions.index'),
            'metadata' => $metadata,
            'payment_intent_data' => ['metadata' => $metadata],
        ]);
    }

    public static function retrieveSession(string $sessionId): ?Session
    {
        try {
            return Cashier::stripe()->checkout->sessions->retrieve($sessionId);
        } catch (Throwable) {
            return null;
        }
    }

    public static function fulfilPromotion(Session $session): ?PromotionOrder
    {
        $metadata = (array) ($session->metadata?->toArray() ?? []);

        if (($metadata['purpose'] ?? null) !== self::PURPOSE_PROMOTION) {
            return null;
        }

        if (($session->payment_status ?? null) !== 'paid') {
            return null;
        }

        $plan = PromotionPlan::query()->find((int) ($metadata['plan_id'] ?? 0));

        if (! $plan instanceof PromotionPlan) {
            return null;
        }

        $currency = (string) ($metadata['currency'] ?? $plan->getAttribute('currency'));
        $expected = (int) round($plan->priceFor($currency) * 100);

        if ((int) ($session->amount_total ?? 0) < $expected || strtoupper((string) ($session->currency ?? '')) !== strtoupper($plan->currencyFor($currency))) {
            return null;
        }

        return PromotionOrder::open(
            $plan,
            (int) ($metadata['user_id'] ?? 0),
            (int) ($metadata['listing_id'] ?? 0),
            'stripe:'.(string) $session->id,
            $currency,
        );
    }

    public static function creditReference(): string
    {
        return 'credit:'.(string) Str::uuid();
    }
}
