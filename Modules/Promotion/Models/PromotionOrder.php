<?php

declare(strict_types=1);

namespace Modules\Promotion\Models;

use App\Support\ListingDirectory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class PromotionOrder extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'listing_id',
        'plan_id',
        'plan_name',
        'amount',
        'currency',
        'payment_reference',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(fn (Builder $inner): Builder => $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForListing(Builder $query, int $listingId): Builder
    {
        return $query->where('listing_id', $listingId);
    }

    public static function open(PromotionPlan $plan, int $userId, int $listingId, ?string $paymentReference = null, ?string $currency = null): self
    {
        if ($paymentReference !== null) {
            $existing = static::query()->where('payment_reference', $paymentReference)->first();

            if ($existing instanceof self) {
                return $existing;
            }
        }

        $order = static::query()->make([
            'user_id' => $userId,
            'listing_id' => $listingId,
            'plan_id' => $plan->getKey(),
            'plan_name' => (string) $plan->getAttribute('name'),
            'amount' => $plan->priceFor((string) ($currency ?? $plan->getAttribute('currency'))),
            'currency' => $plan->currencyFor((string) ($currency ?? $plan->getAttribute('currency'))),
            'payment_reference' => $paymentReference,
            'status' => self::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays((int) $plan->getAttribute('duration_days')),
        ]);

        $order->save();

        if ((bool) $plan->getAttribute('grants_featured')) {
            ListingDirectory::setFeatured([$listingId], true);
        }

        if ((bool) $plan->getAttribute('grants_urgent')) {
            ListingDirectory::setUrgent([$listingId], true);
        }

        if ((int) $plan->getAttribute('bump_count') > 0) {
            ListingDirectory::bump([$listingId]);
        }

        self::keepListingLive($listingId, $order->getAttribute('ends_at'));

        return $order;
    }

    private static function keepListingLive(int $listingId, mixed $until): void
    {
        try {
            $listing = \Modules\Listing\Models\Listing::query()->find($listingId);

            if ($listing === null || $until === null) {
                return;
            }

            $until = Carbon::parse($until)->addDay();
            $changes = [];

            if ($listing->getAttribute('expires_at') === null || $listing->getAttribute('expires_at')->lt($until)) {
                $changes['expires_at'] = $until;
            }

            if ($listing->statusValue() === 'expired') {
                $changes['status'] = 'active';
            }

            if ($changes !== []) {
                $listing->forceFill($changes)->saveQuietly();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public static function countFreeRedemptionsThisMonth(int $userId, int $planId): int
    {
        return (int) static::query()
            ->forUser($userId)
            ->where('plan_id', $planId)
            ->where('payment_reference', 'like', 'credit:%')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    public static function paginateForUser(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return static::query()
            ->forUser($userId)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function activeCountForUser(int $userId): int
    {
        return (int) static::query()->forUser($userId)->active()->count();
    }

    public static function expireOutdated(): void
    {
        $outdated = static::query()
            ->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now());
        $listingIds = (clone $outdated)->distinct()->pluck('listing_id')->map(static fn (mixed $id): int => (int) $id)->all();

        if ($listingIds === []) {
            return;
        }

        $outdated->update(['status' => self::STATUS_EXPIRED]);

        $stillFeatured = self::listingIdsWithActiveGrant($listingIds, 'grants_featured');
        $stillUrgent = self::listingIdsWithActiveGrant($listingIds, 'grants_urgent');

        ListingDirectory::setFeatured(array_values(array_diff($listingIds, $stillFeatured)), false);
        ListingDirectory::setUrgent(array_values(array_diff($listingIds, $stillUrgent)), false);
    }

    private static function listingIdsWithActiveGrant(array $listingIds, string $grant): array
    {
        $planIds = PromotionPlan::query()->where($grant, true)->pluck('id')->all();

        if ($planIds === []) {
            return [];
        }

        return static::query()->active()->whereIn('listing_id', $listingIds)->whereIn('plan_id', $planIds)
            ->pluck('listing_id')->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();
    }

    public function listingId(): int
    {
        return (int) $this->getAttribute('listing_id');
    }

    public function isRunning(): bool
    {
        if ((string) $this->getAttribute('status') !== self::STATUS_ACTIVE) {
            return false;
        }

        $endsAt = $this->getAttribute('ends_at');

        return ! $endsAt instanceof Carbon || $endsAt->isFuture();
    }

    public function remainingDays(): int
    {
        $endsAt = $this->getAttribute('ends_at');

        if (! $endsAt instanceof Carbon) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($endsAt->startOfDay(), false));
    }

    public function amountLabel(): string
    {
        $amount = number_format((float) $this->getAttribute('amount'), 2);

        $symbols = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'];
        $currency = (string) $this->getAttribute('currency');

        return isset($symbols[$currency]) ? $symbols[$currency].$amount : $amount.' '.$currency;
    }
}
