<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;

final class SpamSignals
{
    private const HOURLY_LIMIT = 10;

    public static function guardRate(Listing $listing): void
    {
        $userId = $listing->getAttribute('user_id');

        if ($listing->exists || $userId === null) {
            return;
        }

        $recent = Listing::query()->withTrashed()
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recent >= self::HOURLY_LIMIT) {
            throw ValidationException::withMessages([
                'listingTitle' => __('catmarket::messages.spam_rate'),
            ]);
        }
    }

    public static function reasons(Listing $listing): array
    {
        $title = (string) $listing->getAttribute('title');
        $description = (string) $listing->getAttribute('description');
        $text = $title."\n".$description;
        $userId = $listing->getAttribute('user_id');
        $reasons = [];

        if (preg_match('~https?://|www\.|\b[a-z0-9-]+\.(com|net|org|co\.uk|xyz|shop|info|site|online|io)\b~i', $text) === 1) {
            $reasons[] = __('catmarket::messages.spam_link');
        }

        if (preg_match('~[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}~i', $text) === 1
            || self::hasPhoneNumber($text)
            || preg_match('~\b(whats\s?app|telegram|signal app|wechat|cash\s?app|zelle|western union|gift ?cards?|crypto|bitcoin)\b~i', $text) === 1) {
            $reasons[] = __('catmarket::messages.spam_contact');
        }

        $normalised = Str::of($description)->lower()->squish()->toString();

        if (mb_strlen($normalised) >= 40) {
            $duplicates = Listing::query()
                ->when($listing->exists, static fn ($query) => $query->whereKeyNot($listing->getKey()))
                ->where('created_at', '>=', now()->subDays(30))
                ->whereRaw('LOWER(description) = ?', [mb_strtolower(trim($description))])
                ->count();

            if ($duplicates > 0) {
                $reasons[] = __('catmarket::messages.spam_duplicate');
            }
        }

        if ($userId !== null) {
            $user = User::query()->find($userId);
            $created = $user?->getAttribute('created_at');

            if ($created !== null && $created->gt(now()->subDay())) {
                $sameDay = Listing::query()->where('user_id', $userId)->where('created_at', '>=', now()->subDay())->count();

                if ($sameDay >= 3) {
                    $reasons[] = __('catmarket::messages.spam_new_account', ['count' => $sameDay + 1]);
                }
            }
        }

        if (self::isPedigreeCheap($listing)) {
            $reasons[] = __('catmarket::messages.spam_cheap');
        }

        return $reasons;
    }

    private static function hasPhoneNumber(string $text): bool
    {
        preg_match_all('~\+?\d[\d\s().-]{8,}\d~', $text, $matches);

        foreach ($matches[0] as $candidate) {
            if (strlen((string) preg_replace('~\D~', '', $candidate)) >= 10) {
                return true;
            }
        }

        return false;
    }

    private static function isPedigreeCheap(Listing $listing): bool
    {
        $price = (float) $listing->getAttribute('price');
        $categoryId = $listing->getAttribute('category_id');

        if ($price <= 0 || $price >= 150 || $categoryId === null) {
            return false;
        }

        $category = Category::query()->find($categoryId);
        $slug = (string) $category?->getAttribute('slug');

        return in_array(PetCatalog::leaf($slug)['kind'] ?? null, [PetCatalog::KIND_PEDIGREE, PetCatalog::KIND_LITTER], true);
    }
}
