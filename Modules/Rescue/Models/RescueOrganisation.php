<?php

declare(strict_types=1);

namespace Modules\Rescue\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\CatMarket\Models\SellerVerification;
use Modules\User\App\Models\User;

class RescueOrganisation extends Model
{
    use SoftDeletes;

    public const STATUS_UNVERIFIED = 'unverified';

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name', 'country', 'town', 'species', 'charity_number', 'website', 'contact_email',
        'description', 'accepts_fosters', 'takes_rehoming_requests',
    ];

    protected $casts = [
        'species' => 'array',
        'accepts_fosters' => 'boolean',
        'takes_rehoming_requests' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_UNVERIFIED => 'Unverified',
            self::STATUS_PENDING => 'Waiting for checks',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public static function speciesOptions(): array
    {
        return [
            'cat' => 'Cats', 'dog' => 'Dogs', 'rabbit' => 'Rabbits', 'small' => 'Small animals',
            'bird' => 'Birds', 'reptile' => 'Reptiles', 'fish' => 'Fish', 'other' => 'Other animals',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class, 'organisation_id');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeInCountry(Builder $query, ?string $country): Builder
    {
        return $country === null || $country === '' ? $query : $query->where('country', $country);
    }

    public function scopeCaringFor(Builder $query, ?string $species): Builder
    {
        return $species === null || $species === '' ? $query : $query->whereJsonContains('species', $species);
    }

    public static function forUser(?int $userId): ?self
    {
        return $userId === null ? null : static::query()->where('user_id', $userId)->first();
    }

    public static function verifiedForUser(?int $userId): ?self
    {
        return $userId === null ? null : static::query()->verified()->where('user_id', $userId)->first();
    }

    public static function verifiedUserIds(): array
    {
        return Cache::remember('rescue:verified-users', now()->addMinutes(10), static fn (): array => static::query()
            ->verified()
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all());
    }

    public static function register(User $user, array $data): self
    {
        $organisation = static::forUser((int) $user->getKey()) ?? new static();
        $organisation->fill($data);
        $organisation->forceFill([
            'user_id' => $user->getKey(),
            'slug' => $organisation->getAttribute('slug') ?: static::uniqueSlug((string) $data['name']),
        ]);

        if ($organisation->getAttribute('status') !== self::STATUS_VERIFIED) {
            $organisation->setAttribute('status', self::STATUS_PENDING);
        }

        $organisation->save();

        return $organisation;
    }

    public function approve(?int $adminId): void
    {
        $this->forceFill([
            'status' => self::STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $adminId,
        ])->save();

        if ($this->getAttribute('user_id') !== null && class_exists(SellerVerification::class)) {
            $verification = SellerVerification::query()->firstOrNew([
                'user_id' => $this->getAttribute('user_id'),
                'kind' => SellerVerification::KIND_RESCUE,
            ]);
            $verification->fill([
                'reference' => $this->getAttribute('charity_number'),
                'issuing_authority' => $this->getAttribute('country') === 'US' ? 'IRS (EIN)' : 'Charity regulator',
                'verified_by' => $adminId,
                'verified_at' => now(),
            ])->save();
        }

        Cache::forget('rescue:verified-users');
    }

    public function suspend(): void
    {
        $this->forceFill(['status' => self::STATUS_SUSPENDED])->save();

        if ($this->getAttribute('user_id') !== null && class_exists(SellerVerification::class)) {
            SellerVerification::query()
                ->where('user_id', $this->getAttribute('user_id'))
                ->where('kind', SellerVerification::KIND_RESCUE)
                ->update(['verified_at' => null]);
        }

        Cache::forget('rescue:verified-users');
    }

    public function isVerified(): bool
    {
        return $this->getAttribute('status') === self::STATUS_VERIFIED;
    }

    public function speciesLabels(): array
    {
        return collect((array) ($this->getAttribute('species') ?? []))
            ->map(static fn (string $key): string => self::speciesOptions()[$key] ?? $key)
            ->values()
            ->all();
    }

    public function statusLabel(): string
    {
        return self::statuses()[(string) $this->getAttribute('status')] ?? (string) $this->getAttribute('status');
    }

    private static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'rescue';
        $slug = $base;
        $suffix = 2;

        while (static::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
