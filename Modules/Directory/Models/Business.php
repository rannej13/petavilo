<?php

declare(strict_types=1);

namespace Modules\Directory\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\User\App\Models\User;

class Business extends Model
{
    use SoftDeletes;

    public const TIERS = ['basic' => 1, 'plus' => 2, 'premium' => 3];

    protected $fillable = [
        'name', 'type', 'country', 'description', 'address_line', 'town', 'postcode', 'phone', 'email',
        'website', 'opening_hours', 'licence_number', 'owner_user_id', 'tier', 'is_verified', 'is_published',
        'latitude', 'longitude', 'social_url', 'services', 'price_from', 'accreditation', 'emergency_care', 'home_visits',
        'cat_only', 'new_client_offer', 'booking_url',
    ];

    public const ACCREDITATIONS = [
        'isfm_gold' => 'ISFM Cat Friendly Clinic: Gold',
        'isfm_silver' => 'ISFM Cat Friendly Clinic: Silver',
        'isfm_bronze' => 'ISFM Cat Friendly Clinic: Bronze',
        'aafp_cfp' => 'AAFP Cat Friendly Practice',
        'icc_cat_care' => 'International Cat Care trained',
        'rcvs_psc' => 'RCVS accredited practice',
    ];

    public const FREE_PHOTOS = 3;

    public const PAID_PHOTOS = 10;

    protected $casts = [
        'is_verified' => 'boolean',
        'is_published' => 'boolean',
        'claimed_at' => 'datetime',
        'tier_rank' => 'integer',
        'view_count' => 'integer',
        'website_clicks' => 'integer',
        'services' => 'array',
        'photos' => 'array',
        'emergency_care' => 'boolean',
        'home_visits' => 'boolean',
        'cat_only' => 'boolean',
        'accreditation_verified' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'invited_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $business): void {
            if (blank($business->getAttribute('slug')) || $business->isDirty('name')) {
                $business->setAttribute('slug', self::uniqueSlug((string) $business->getAttribute('name'), (string) $business->getAttribute('town'), $business));
            }

            $business->setAttribute('dedupe_key', self::dedupeKeyFor(
                (string) $business->getAttribute('country'),
                (string) $business->getAttribute('name'),
                $business->getAttribute('postcode'),
                $business->getAttribute('town'),
            ));

            $tier = (string) ($business->getAttribute('tier') ?: 'basic');
            $business->setAttribute('tier_rank', self::TIERS[$tier] ?? 1);
        });
    }

    public static function types(): array
    {
        return collect((array) config('directory.types', []))
            ->map(static fn (array $type): string => (string) $type['name'])
            ->all();
    }

    public static function typeName(string $type, bool $singular = false): string
    {
        $definition = (array) (config('directory.types')[$type] ?? []);

        return (string) ($definition[$singular ? 'singular' : 'name'] ?? Str::headline($type));
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeRanked(Builder $query): Builder
    {
        return $query->orderByDesc('tier_rank')->orderByDesc('is_verified')->orderBy('name');
    }

    public static function countries(): array
    {
        return ['GB' => 'United Kingdom', 'US' => 'United States'];
    }

    public static function browse(string $type, string $town, string $search, string $country = 'GB'): LengthAwarePaginator
    {
        return static::query()
            ->published()
            ->where('type', $type)
            ->where('country', $country)
            ->when($town !== '', static fn (Builder $query): Builder => $query->where('town', 'ilike', $town.'%'))
            ->when($search !== '', static fn (Builder $query): Builder => $query->where(static fn (Builder $inner): Builder => $inner
                ->where('name', 'ilike', '%'.$search.'%')
                ->orWhere('postcode', 'ilike', $search.'%')
                ->orWhere('town', 'ilike', '%'.$search.'%')))
            ->ranked()
            ->paginate((int) config('directory.per_page', 20))
            ->withQueryString();
    }

    public static function featured(int $limit = 6, string $country = 'GB'): Collection
    {
        return static::query()->published()->where('country', $country)->where('tier', 'premium')->inRandomOrder()->limit($limit)->get();
    }

    public static function countsByType(string $country = 'GB'): array
    {
        return \Illuminate\Support\Facades\Cache::remember('directory-counts:'.$country, now()->addMinutes(30), static fn (): array => static::query()->published()->where('country', $country)
            ->selectRaw('type, COUNT(*) as aggregate')->groupBy('type')
            ->pluck('aggregate', 'type')->map(static fn (mixed $count): int => (int) $count)->all());
    }

    public static function towns(string $country = 'GB', ?string $type = null, int $limit = 300): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'directory-towns:'.$country.':'.($type ?? 'all').':'.$limit,
            now()->addHours(6),
            static fn (): array => static::query()->published()
                ->where('country', $country)
                ->when($type !== null, static fn (Builder $query): Builder => $query->where('type', $type))
                ->whereNotNull('town')->where('town', '!=', '')
                ->selectRaw('town, COUNT(*) as aggregate')
                ->groupBy('town')
                ->orderByDesc('aggregate')
                ->limit($limit)
                ->pluck('town')
                ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all(),
        );
    }

    public static function findPublished(string $type, string $slug): ?self
    {
        return static::query()->published()->where('type', $type)->where('slug', $slug)->first();
    }

    public static function ownedBy(int $userId): Collection
    {
        return static::query()->where('owner_user_id', $userId)->orderBy('name')->get();
    }

    public static function syncTierForOwner(int $userId, string $tier): void
    {
        $tier = array_key_exists($tier, self::TIERS) ? $tier : 'basic';

        static::query()->where('owner_user_id', $userId)->update([
            'tier' => $tier,
            'tier_rank' => self::TIERS[$tier],
        ]);
    }

    public static function claimedOwnerIds(): array
    {
        return static::query()->whereNotNull('owner_user_id')->distinct()->pluck('owner_user_id')
            ->map(static fn (mixed $id): int => (int) $id)->all();
    }

    public function isPaid(): bool
    {
        return in_array((string) $this->getAttribute('tier'), ['plus', 'premium'], true);
    }

    public function isClaimed(): bool
    {
        return $this->getAttribute('owner_user_id') !== null;
    }

    public function websiteUrl(): ?string
    {
        $website = trim((string) $this->getAttribute('website'));

        if ($website === '') {
            return null;
        }

        return Str::startsWith($website, ['http://', 'https://']) ? $website : 'https://'.$website;
    }

    public function trackView(): void
    {
        static::query()->whereKey($this->getKey())->increment('view_count');
    }

    public function updateFromOwner(array $data): void
    {
        $allowed = [
            'description', 'address_line', 'postcode', 'phone', 'email', 'website', 'opening_hours', 'licence_number',
            'social_url', 'services', 'price_from', 'accreditation', 'emergency_care', 'home_visits', 'cat_only',
            'booking_url', 'new_client_offer',
        ];

        if (! $this->isPaid()) {
            $allowed = array_values(array_diff($allowed, ['new_client_offer']));
            $data['description'] = Str::limit((string) ($data['description'] ?? ''), 300, '');
        }

        if (array_key_exists('accreditation', $data) && $data['accreditation'] !== $this->getAttribute('accreditation')) {
            $this->setAttribute('accreditation_verified', false);
        }

        $this->fill(array_intersect_key($data, array_flip($allowed)))->save();
    }

    public static function dedupeKeyFor(string $country, string $name, mixed $postcode, mixed $town): ?string
    {
        $cleanName = Str::of($name)->lower()->ascii()
            ->replaceMatches('/\b(the|ltd|limited|llc|inc|plc|llp|co|company|&|and)\b/', ' ')
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();

        if ($cleanName === '') {
            return null;
        }

        $place = Str::of((string) $postcode)->upper()->replaceMatches('/[^A-Z0-9]/', '')->toString();

        if ($place === '') {
            $place = Str::of((string) $town)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
        }

        return $place === '' ? null : Str::limit(strtoupper($country ?: 'GB').':'.$cleanName.':'.$place, 160, '');
    }

    public function photoUrls(): array
    {
        return collect((array) ($this->getAttribute('photos') ?? []))
            ->filter(static fn (mixed $path): bool => is_string($path) && $path !== '')
            ->map(static fn (string $path): string => asset('storage/'.ltrim($path, '/')))
            ->values()
            ->all();
    }

    public function logoUrl(): ?string
    {
        $path = trim((string) $this->getAttribute('logo_path'));

        return $path === '' ? null : asset('storage/'.ltrim($path, '/'));
    }

    public function photoLimit(): int
    {
        return $this->isPaid() ? self::PAID_PHOTOS : self::FREE_PHOTOS;
    }

    public function accreditationLabel(): ?string
    {
        $key = (string) $this->getAttribute('accreditation');

        return self::ACCREDITATIONS[$key] ?? null;
    }

    public function serviceList(): array
    {
        return collect((array) ($this->getAttribute('services') ?? []))
            ->map(static fn (mixed $item): string => trim((string) $item))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }

    public function bookingUrl(): ?string
    {
        $url = trim((string) $this->getAttribute('booking_url'));

        if ($url === '') {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : 'https://'.$url;
    }

    public function profileCompleteness(): int
    {
        $checks = [
            filled($this->getAttribute('description')),
            filled($this->getAttribute('phone')),
            filled($this->getAttribute('website')),
            filled($this->getAttribute('email')),
            filled($this->getAttribute('opening_hours')),
            $this->serviceList() !== [],
            filled($this->getAttribute('logo_path')),
            $this->photoUrls() !== [],
            filled($this->getAttribute('address_line')),
            filled($this->getAttribute('booking_url')),
        ];

        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }

    private static function uniqueSlug(string $name, string $town, self $record): string
    {
        $base = Str::slug(trim($name.' '.$town)) ?: 'business';
        $slug = $base;
        $counter = 2;

        while (static::query()->where('slug', $slug)->when($record->exists, static fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
