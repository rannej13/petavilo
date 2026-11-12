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

        return in_array($slug, (array) config('catmarket.animal_roots', []), true) ? $slug : null;
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

        if ($isUk && $sellerType === CatCatalog::SELLER_LICENSED) {
            foreach (['licence_number', 'licence_council'] as $suffix) {
                if (blank(self::find($values, $suffix))) {
                    $errors[self::keyFor($values, $suffix)] = __('catmarket::messages.licence_required');
                }
            }
        }

        if ($isUk && $sellerType === CatCatalog::SELLER_RESCUE && blank(self::find($values, 'charity_number'))) {
            $errors[self::keyFor($values, 'charity_number')] = __('catmarket::messages.charity_required');
        }

        $birth = self::date(self::find($values, 'date_of_birth'));

        if ($birth !== null && $birth->isFuture() && $root !== 'pedigree-cats') {
            $errors[self::keyFor($values, 'date_of_birth')] = __('catmarket::messages.birth_in_future');
        }

        $minWeeks = (int) config('catmarket.min_leave_weeks', 8);
        $readyToLeave = self::date(self::find($values, 'ready_to_leave'));

        if ($birth !== null && $readyToLeave !== null && $readyToLeave->lt($birth->copy()->addWeeks($minWeeks))) {
            $errors[self::keyFor($values, 'ready_to_leave')] = __('catmarket::messages.too_young', ['weeks' => $minWeeks]);
        }

        $chipWeeks = (int) config('catmarket.microchip_weeks', 20);

        if ($isUk && $birth !== null && $birth->lte(now()->subWeeks($chipWeeks)) && ! self::truthy(self::find($values, 'microchipped'))) {
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

        if (in_array($sellerType, [CatCatalog::SELLER_LICENSED, CatCatalog::SELLER_REGISTERED_BREEDER, CatCatalog::SELLER_RESCUE], true)) {
            $reasons[] = __('catmarket::messages.hold_verify_seller', ['type' => $sellerType]);
        }

        if (array_key_exists(self::keyFor($values, 'mother_seen'), $values) && ! self::truthy(self::find($values, 'mother_seen'))) {
            $reasons[] = __('catmarket::messages.hold_no_mother');
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
