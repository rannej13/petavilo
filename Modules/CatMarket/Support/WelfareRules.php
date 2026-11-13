<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Throwable;

final class WelfareRules
{
    public static function animalRootSlug(?int $categoryId): ?string
    {
        if ($categoryId === null || $categoryId <= 0) {
            return null;
        }

        $category = Category::query()->find($categoryId);

        if (! $category instanceof Category) {
            return null;
        }

        $root = $category->breadcrumbTrail()->first();
        $slug = (string) ($root?->getAttribute('slug') ?? $category->getAttribute('slug'));

        return in_array($slug, PetCatalog::animalRootSlugs(), true) ? $slug : null;
    }

    public static function leafKind(?int $categoryId): ?string
    {
        if ($categoryId === null || $categoryId <= 0) {
            return null;
        }

        $slug = (string) Category::query()->whereKey($categoryId)->value('slug');

        return PetCatalog::leaf($slug)['kind'] ?? null;
    }

    public static function isAnimalCategory(?int $categoryId): bool
    {
        return self::animalRootSlug($categoryId) !== null;
    }

    public static function errors(?int $categoryId, array $values, string $title = '', string $description = '', ?string $country = null): array
    {
        $root = self::animalRootSlug($categoryId);
        $isUk = in_array(CatMarketSettings::countryCodeFor($country), ['GB', ''], true);
        $errors = [];

        foreach (self::matchedPhrases((array) config('catmarket.blocked_phrases', []), $title.' '.$description) as $phrase) {
            $errors['description'] = __('catmarket::messages.blocked_phrase', ['phrase' => $phrase]);
        }

        if ($root === null) {
            return $errors;
        }

        $sellerType = (string) (self::find($values, 'seller_type') ?? '');
        $species = PetCatalog::speciesForRoot($root);
        $kind = self::leafKind($categoryId);

        if ($isUk && $sellerType === PetCatalog::SELLER_LICENSED) {
            foreach (['licence_number', 'licence_council'] as $suffix) {
                if (blank(self::find($values, $suffix))) {
                    $errors[self::keyFor($values, $suffix)] = __('catmarket::messages.licence_required');
                }
            }
        }

        if ($isUk && $sellerType === PetCatalog::SELLER_RESCUE && blank(self::find($values, 'charity_number'))) {
            $errors[self::keyFor($values, 'charity_number')] = __('catmarket::messages.charity_required');
        }

        $birth = self::date(self::find($values, 'date_of_birth'));

        if ($birth !== null && $birth->isFuture() && $kind !== PetCatalog::KIND_LITTER) {
            $errors[self::keyFor($values, 'date_of_birth')] = __('catmarket::messages.birth_in_future');
        }

        $minWeeks = (int) config('catmarket.min_leave_weeks', 8);
        $readyToLeave = self::date(self::find($values, 'ready_to_leave'));

        if (in_array($species, ['cat', 'dog', 'rabbit'], true) && $birth !== null && $readyToLeave !== null && $readyToLeave->lt($birth->copy()->addWeeks($minWeeks))) {
            $errors[self::keyFor($values, 'ready_to_leave')] = __('catmarket::messages.too_young', ['weeks' => $minWeeks]);
        }

        $chipWeeks = match ($species) {
            'cat' => (int) config('catmarket.microchip_weeks', 20),
            'dog' => 8,
            default => null,
        };
        $chipped = self::truthy(self::find($values, 'microchipped')) || self::find($values, 'microchip') === 'Yes';

        if ($isUk && $chipWeeks !== null && $birth !== null && $birth->lte(now()->subWeeks($chipWeeks)) && ! $chipped && self::find($values, 'microchip') !== 'Unknown') {
            $errors[self::keyFor($values, 'microchipped')] = __('catmarket::messages.microchip_required', ['weeks' => $chipWeeks]);
        }

        return $errors;
    }

    public static function holdReasons(?int $categoryId, array $values, string $title = '', string $description = ''): array
    {
        if (! self::isAnimalCategory($categoryId)) {
            return [];
        }

        $reasons = [__('catmarket::messages.hold_animal')];

        foreach (self::matchedPhrases((array) config('catmarket.held_phrases', []), $title.' '.$description) as $phrase) {
            $reasons[] = __('catmarket::messages.hold_phrase', ['phrase' => $phrase]);
        }

        $sellerType = (string) (self::find($values, 'seller_type') ?? '');

        if (in_array($sellerType, [PetCatalog::SELLER_LICENSED, PetCatalog::SELLER_REGISTERED_BREEDER, PetCatalog::SELLER_RESCUE], true)) {
            $reasons[] = __('catmarket::messages.hold_verify_seller', ['type' => $sellerType]);
        }

        if (array_key_exists(self::keyFor($values, 'mother_seen'), $values) && ! self::truthy(self::find($values, 'mother_seen'))) {
            $reasons[] = __('catmarket::messages.hold_no_mother');
        }

        $mismatch = self::ageMismatch(self::date(self::find($values, 'date_of_birth')), $title.' '.$description);

        if ($mismatch !== null) {
            $reasons[] = $mismatch;
        }

        return array_values(array_unique($reasons));
    }

    public static function find(array $values, string $suffix): mixed
    {
        foreach ($values as $key => $value) {
            if ((string) $key === $suffix || Str::endsWith((string) $key, '_'.$suffix)) {
                return $value;
            }
        }

        return null;
    }

    public static function keyFor(array $values, string $suffix): string
    {
        foreach (array_keys($values) as $key) {
            if ((string) $key === $suffix || Str::endsWith((string) $key, '_'.$suffix)) {
                return (string) $key;
            }
        }

        return $suffix;
    }

    public static function ageMismatch(?Carbon $birth, string $text): ?string
    {
        if ($birth === null || preg_match('/\b(\d{1,2})\s*(week|wk|month|year|yr)s?\s*old\b/i', $text, $match) !== 1) {
            return null;
        }

        $stated = (int) $match[1];
        $unit = Str::lower($match[2]);
        $actual = match (true) {
            str_starts_with($unit, 'w') => (int) $birth->diffInWeeks(now()),
            str_starts_with($unit, 'm') => (int) $birth->diffInMonths(now()),
            default => (int) $birth->diffInYears(now()),
        };
        $tolerance = str_starts_with($unit, 'w') ? 2 : 1;

        if (abs($actual - $stated) <= $tolerance) {
            return null;
        }

        return 'Age check: the text says '.$match[0].' but the date of birth makes it about '.$actual.' '.(str_starts_with($unit, 'w') ? 'weeks' : (str_starts_with($unit, 'm') ? 'months' : 'years')).'. Please check before approving.';
    }

    private static function matchedPhrases(array $phrases, string $text): array
    {
        $haystack = ' '.Str::lower(preg_replace('/\s+/', ' ', $text) ?? '').' ';

        return array_values(array_filter(
            $phrases,
            static fn (string $phrase): bool => preg_match('/\b'.preg_quote(Str::lower($phrase), '/').'\b/u', $haystack) === 1,
        ));
    }

    private static function date(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    private static function truthy(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes', 'Yes'], true);
    }
}
