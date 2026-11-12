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
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_published' => 'boolean',
        'claimed_at' => 'datetime',
        'tier_rank' => 'integer',
        'view_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $business): void {
            if (blank($business->getAttribute('slug')) || $business->isDirty('name')) {
                $business->setAttribute('slug', self::uniqueSlug((string) $business->getAttribute('name'), (string) $business->getAttribute('town'), $business));
            }

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
            ->when($town !== '', static fn (Builder $query): Builder => $query->where('town', 'ilike', $town))
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
        return static::query()->published()->where('country', $country)
            ->selectRaw('type, COUNT(*) as aggregate')->groupBy('type')
            ->pluck('aggregate', 'type')->map(static fn (mixed $count): int => (int) $count)->all();
    }

    public static function towns(string $country = 'GB'): array
    {
        return static::query()->published()->where('country', $country)->whereNotNull('town')->distinct()->orderBy('town')->pluck('town')->all();
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
        $allowed = ['description', 'address_line', 'phone', 'email', 'website', 'opening_hours', 'licence_number'];

        if (! $this->isPaid()) {
            $allowed = array_values(array_diff($allowed, ['opening_hours']));
            $data['description'] = Str::limit((string) ($data['description'] ?? ''), 300, '');
        }

        $this->fill(array_intersect_key($data, array_flip($allowed)))->save();
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
