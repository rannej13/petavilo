<?php

declare(strict_types=1);

namespace Modules\CatMarket\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimalListingReview extends Model
{
    use SoftDeletes;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    protected $fillable = ['listing_id', 'user_id', 'reasons', 'decision', 'note', 'decided_by', 'submitted_at', 'decided_at'];

    protected $casts = [
        'reasons' => 'array',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function scopePending(Builder $query): Builder
    {
        return $query->where('decision', self::PENDING);
    }

    public static function submit(int $listingId, ?int $userId, array $reasons): self
    {
        return static::query()->updateOrCreate(
            ['listing_id' => $listingId],
            [
                'user_id' => $userId,
                'reasons' => $reasons,
                'decision' => self::PENDING,
                'note' => null,
                'decided_by' => null,
                'submitted_at' => now(),
                'decided_at' => null,
            ],
        );
    }

    public static function decide(int $listingId, string $decision, ?int $adminId, ?string $note = null): void
    {
        static::query()->where('listing_id', $listingId)->update([
            'decision' => $decision,
            'note' => $note,
            'decided_by' => $adminId,
            'decided_at' => now(),
        ]);
    }

    public static function reasonsFor(int $listingId): array
    {
        $review = static::query()->where('listing_id', $listingId)->first();

        return $review instanceof self ? (array) $review->getAttribute('reasons') : [];
    }

    public static function pendingCount(): int
    {
        return (int) static::query()->pending()->count();
    }

    public static function pendingListingIds(): array
    {
        return static::query()->pending()->pluck('listing_id')->map(static fn (mixed $id): int => (int) $id)->all();
    }
}
