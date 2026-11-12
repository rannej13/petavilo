<?php

declare(strict_types=1);

namespace Modules\CatMarket\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\App\Models\User;

class SellerVerification extends Model
{
    use SoftDeletes;

    public const KIND_LICENSED = 'licensed_seller';

    public const KIND_BREEDER = 'registered_breeder';

    public const KIND_RESCUE = 'rescue';

    public const KIND_BUSINESS = 'business';

    protected $fillable = ['user_id', 'kind', 'reference', 'issuing_authority', 'expires_on', 'note', 'verified_by', 'verified_at'];

    protected $casts = [
        'expires_on' => 'date',
        'verified_at' => 'datetime',
    ];

    public static function kinds(): array
    {
        return [
            self::KIND_LICENSED => 'Licensed seller (council licence)',
            self::KIND_BREEDER => 'Registered breeder',
            self::KIND_RESCUE => 'Rescue or charity',
            self::KIND_BUSINESS => 'Business',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->whereNotNull('verified_at')
            ->where(fn (Builder $inner): Builder => $inner->whereNull('expires_on')->orWhere('expires_on', '>=', now()->toDateString()));
    }

    public static function badgesFor(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }

        return static::query()
            ->current()
            ->where('user_id', $userId)
            ->get(['kind', 'reference', 'issuing_authority'])
            ->map(static fn (self $verification): array => [
                'kind' => (string) $verification->getAttribute('kind'),
                'label' => self::kinds()[(string) $verification->getAttribute('kind')] ?? (string) $verification->getAttribute('kind'),
                'reference' => $verification->getAttribute('reference'),
                'authority' => $verification->getAttribute('issuing_authority'),
            ])
            ->all();
    }
}
