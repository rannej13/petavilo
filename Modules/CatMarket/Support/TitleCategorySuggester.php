<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Str;

final class TitleCategorySuggester
{
    private const KEYWORDS = [
        'lost-and-found-lost-cats' => ['lost', 'missing', 'escaped', 'runaway'],
        'lost-and-found-found-cats' => ['found', 'stray', 'turned up', 'wandered'],
        'cat-products-cat-trees-furniture' => ['cat tree', 'tree', 'tower', 'scratch', 'scratching', 'post', 'bed', 'furniture', 'hammock', 'condo', 'shelf'],
        'cat-products-carriers-travel' => ['carrier', 'crate', 'travel', 'backpack', 'pet bag', 'stroller'],
        'cat-products-toys' => ['toy', 'toys', 'wand', 'teaser', 'tunnel', 'laser', 'kicker', 'catnip'],
        'cat-products-bowls-feeders' => ['bowl', 'bowls', 'feeder', 'fountain', 'water dispenser'],
        'cat-products-litter-trays' => ['litter tray', 'litter box', 'tray', 'litter mat', 'self cleaning'],
        'cat-products-other-products' => ['food', 'harness', 'collar', 'cat flap', 'flap', 'brush', 'pen', 'playpen', 'enclosure', 'cage', 'gate'],
        'cat-services-cat-sitting' => ['sitter', 'sitting', 'pet sitter', 'drop in', 'feeding visits', 'house sit'],
        'cat-services-grooming' => ['groom', 'groomer', 'grooming', 'nail', 'bath'],
        'cat-services-boarding' => ['boarding', 'cattery', 'holiday'],
        'cat-services-behaviour-training' => ['behaviour', 'behavior', 'behaviourist', 'training', 'trainer'],
        'cat-services-pet-transport' => ['transport', 'courier', 'relocation', 'pet taxi'],
        'pedigree-cats-upcoming-litters' => ['upcoming litter', 'expected', 'due', 'pregnant', 'taking deposits', 'waiting list', 'reserve now'],
        'kittens-pedigree-kittens' => ['pedigree kitten', 'gccf', 'tica', 'cfa', 'registered kitten', 'pedigree'],
        'kittens-non-pedigree-kittens' => ['kitten', 'kittens', 'kitty', 'moggy kitten'],
        'pedigree-cats-adult-pedigree-cats' => ['stud', 'queen', 'retired', 'breeding cat'],
        'cats-for-rehoming-senior-cats' => ['senior', 'elderly', 'old cat', 'older'],
        'cats-for-rehoming-bonded-pairs' => ['pair', 'bonded', 'brothers', 'sisters', 'siblings', 'two cats', '2 cats'],
        'cats-for-rehoming-adult-cats' => ['rehome', 'rehoming', 'adopt', 'adoption', 'free to good home', 'needs a home', 'cat', 'tom', 'female cat', 'male cat'],
    ];

    public static function suggest(string $title, int $limit = 3): array
    {
        $text = ' '.Str::of($title)->lower()->replaceMatches('/[^a-z0-9 ]+/', ' ')->squish().' ';

        if (trim($text) === '' || mb_strlen(trim($text)) < 3) {
            return [];
        }

        $scores = [];

        foreach (self::KEYWORDS as $slug => $words) {
            foreach ($words as $word) {
                if (str_contains($text, ' '.$word.' ') || str_contains($text, ' '.$word.'s ')) {
                    $scores[$slug] = ($scores[$slug] ?? 0) + 1 + substr_count($word, ' ');
                }
            }
        }

        $breed = self::mentionsBreed($text);
        $kitten = (bool) preg_match('/ kitt(en|ens|y|ies) /', $text);

        if ($breed) {
            if ($kitten) {
                $scores['kittens-pedigree-kittens'] = ($scores['kittens-pedigree-kittens'] ?? 0) + 3;
            } else {
                $scores['pedigree-cats-adult-pedigree-cats'] = ($scores['pedigree-cats-adult-pedigree-cats'] ?? 0) + 2;
            }
        }

        if (str_contains($text, ' litter ')) {
            if ($kitten || $breed) {
                $scores['pedigree-cats-upcoming-litters'] = ($scores['pedigree-cats-upcoming-litters'] ?? 0) + 1;
                unset($scores['cat-products-litter-trays']);
            } else {
                $scores['cat-products-litter-trays'] = ($scores['cat-products-litter-trays'] ?? 0) + 2;
            }
        }

        $hasSpecific = collect($scores)->keys()->contains(
            fn (string $slug): bool => str_starts_with($slug, 'cat-products') || str_starts_with($slug, 'cat-services') || str_starts_with($slug, 'lost-and-found'),
        );
        if ($hasSpecific) {
            unset($scores['cats-for-rehoming-adult-cats']);
        }

        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }

    private static function mentionsBreed(string $text): bool
    {
        foreach (CatCatalog::breeds() as $breed) {
            $name = Str::of($breed)->lower()->before('(')->trim()->value();
            if (in_array($name, ['other', 'crossbreed', 'domestic shorthair', 'domestic longhair'], true)) {
                continue;
            }
            if (str_contains($text, ' '.$name.' ') || str_contains($text, ' '.$name.'s ')) {
                return true;
            }
        }

        return (bool) preg_match('/ (bsh|blh|maine coon|raggie|ragdoll|sphinx|persian) /', $text);
    }
}
