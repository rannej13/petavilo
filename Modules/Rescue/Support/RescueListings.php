<?php

declare(strict_types=1);

namespace Modules\Rescue\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\CatMarket\Support\PetCatalog;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\Rescue\Models\RescueOrganisation;

final class RescueListings
{
    public static function speciesFilters(): array
    {
        return collect(PetCatalog::categories())
            ->whereNotNull('species')
            ->mapWithKeys(static fn (array $root): array => [$root['species'] => $root['name']])
            ->all();
    }

    public static function adoptable(?string $species, int $perPage = 24): LengthAwarePaginator
    {
        $userIds = RescueOrganisation::verifiedUserIds();

        return self::base(self::leafIds([PetCatalog::KIND_REHOMING], $species))
            ->whereIn('user_id', $userIds === [] ? [0] : $userIds)
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function forOrganisation(RescueOrganisation $organisation, int $limit = 24): \Illuminate\Support\Collection
    {
        return self::base(self::leafIds(PetCatalog::ANIMAL_KINDS, null))
            ->where('user_id', (int) $organisation->getAttribute('user_id'))
            ->limit($limit)
            ->get();
    }

    public static function privateRehoming(?string $species, int $limit = 8): \Illuminate\Support\Collection
    {
        $userIds = RescueOrganisation::verifiedUserIds();

        return self::base(self::leafIds([PetCatalog::KIND_REHOMING], $species))
            ->whereNotIn('user_id', $userIds === [] ? [0] : $userIds)
            ->limit($limit)
            ->get();
    }

    public static function lostAndFound(?string $type, ?string $species, int $perPage = 24): LengthAwarePaginator
    {
        $slugs = match ($type) {
            'lost' => ['lost-and-found-lost-pets'],
            'found' => ['lost-and-found-found-pets'],
            default => ['lost-and-found-lost-pets', 'lost-and-found-found-pets'],
        };
        $ids = Category::query()->whereIn('slug', $slugs)->pluck('id')->all();
        $label = $species === null ? null : (self::lostFoundSpeciesLabels()[$species] ?? null);

        return self::base($ids)
            ->when($label !== null, static fn (Builder $query): Builder => $query->where(static function (Builder $inner) use ($label): void {
                $inner->where('custom_fields->lost_and_found_lost_pets_species', $label)
                    ->orWhere('custom_fields->lost_and_found_found_pets_species', $label);
            }))
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function lostFoundSpeciesLabels(): array
    {
        return ['cat' => 'Cat', 'dog' => 'Dog', 'rabbit' => 'Rabbit', 'small' => 'Small animal', 'bird' => 'Bird', 'reptile' => 'Reptile', 'other' => 'Other'];
    }

    public static function rehomingCategoryId(?string $species): ?int
    {
        $ids = self::leafIds([PetCatalog::KIND_REHOMING], $species);

        return $ids[0] ?? null;
    }

    private static function base(array $categoryIds): Builder
    {
        return Listing::query()
            ->publicFeed()
            ->inVisitorRegion()
            ->whereIn('category_id', $categoryIds === [] ? [0] : $categoryIds)
            ->with(['category:id,name,slug,parent_id', 'media']);
    }

    private static function leafIds(array $kinds, ?string $species): array
    {
        $slugs = collect(PetCatalog::leaves())
            ->filter(static fn (array $leaf): bool => in_array($leaf['kind'], $kinds, true) && ($species === null || $species === '' || $leaf['species'] === $species))
            ->keys()
            ->all();

        return Category::query()->whereIn('slug', $slugs)->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
    }
}
