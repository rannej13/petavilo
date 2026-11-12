<?php

declare(strict_types=1);

namespace Modules\Billing\Support;

use Modules\User\App\Models\User;

final class MembershipCatalog
{
    public static function all(): array
    {
        return (array) config('billing.memberships', []);
    }

    public static function find(string $key): ?array
    {
        $plan = self::all()[$key] ?? null;

        return is_array($plan) ? $plan + ['key' => $key] : null;
    }

    public static function priceIdFor(array $plan, string $currency): ?string
    {
        $key = strtoupper($currency) === 'USD' ? 'price_id_usd' : 'price_id';
        $priceId = trim((string) ($plan[$key] ?? ''));

        return $priceId !== '' ? $priceId : null;
    }

    public static function amountFor(array $plan, string $currency): string
    {
        return strtoupper($currency) === 'USD'
            ? '$'.(string) ($plan['amount_usd'] ?? $plan['amount'])
            : '£'.(string) $plan['amount'];
    }

    public static function priceIds(array $plan): array
    {
        return array_values(array_filter([
            trim((string) ($plan['price_id'] ?? '')),
            trim((string) ($plan['price_id_usd'] ?? '')),
        ]));
    }

    public static function isPurchasable(array $plan, string $currency = 'GBP'): bool
    {
        return PaymentGateway::isConfigured() && self::priceIdFor($plan, $currency) !== null;
    }

    public static function isBreeder(User $user): bool
    {
        $priceIds = self::priceIds((array) (self::all()['breeder'] ?? []));

        return $priceIds !== [] && $user->subscribedToPrice($priceIds, 'breeder');
    }

    public static function directoryTier(User $user): string
    {
        foreach (['directory-premium', 'directory-plus'] as $key) {
            $plan = self::all()[$key] ?? [];
            $priceIds = self::priceIds($plan);

            if ($priceIds !== [] && $user->subscribedToPrice($priceIds, 'directory')) {
                return (string) ($plan['tier'] ?? 'basic');
            }
        }

        return 'basic';
    }

    public static function activeKeys(User $user): array
    {
        return collect(self::all())
            ->filter(static fn (array $plan): bool => self::priceIds($plan) !== []
                && $user->subscribedToPrice(self::priceIds($plan), (string) $plan['type']))
            ->keys()
            ->all();
    }
}
