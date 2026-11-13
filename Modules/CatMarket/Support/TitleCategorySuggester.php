<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Str;

final class TitleCategorySuggester
{
    private const SPECIES_WORDS = [
        'cat' => ['cat', 'cats', 'kitten', 'kittens', 'kitty', 'moggy', 'tabby', 'queen', 'tom'],
        'dog' => ['dog', 'dogs', 'puppy', 'puppies', 'pup', 'pups', 'pooch', 'bitch', 'stud dog'],
        'rabbit' => ['rabbit', 'rabbits', 'bunny', 'bunnies', 'kit', 'doe', 'buck'],
        'small' => ['guinea pig', 'guinea pigs', 'hamster', 'hamsters', 'rat', 'rats', 'mouse', 'mice', 'gerbil', 'gerbils', 'ferret', 'ferrets', 'chinchilla', 'degu', 'degus', 'hedgehog'],
        'bird' => ['bird', 'birds', 'budgie', 'budgies', 'cockatiel', 'canary', 'finch', 'finches', 'parrot', 'lovebird', 'conure', 'african grey', 'hen', 'hens', 'chicken', 'chickens', 'duck', 'ducks', 'pigeon', 'dove'],
        'reptile' => ['reptile', 'bearded dragon', 'beardie', 'gecko', 'snake', 'python', 'corn snake', 'tortoise', 'turtle', 'terrapin', 'chameleon', 'lizard', 'axolotl', 'frog', 'iguana'],
        'fish' => ['fish', 'goldfish', 'koi', 'guppy', 'guppies', 'betta', 'tetra', 'cichlid', 'shrimp', 'pleco'],
    ];

    private const ROOTS = [
        'cat' => 'cats', 'dog' => 'dogs', 'rabbit' => 'rabbits', 'small' => 'small-animals',
        'bird' => 'birds', 'reptile' => 'reptiles', 'fish' => 'fish',
    ];

    private const PRODUCT_WORDS = ['tree', 'tower', 'scratch', 'scratching', 'post', 'bed', 'beds', 'crate', 'carrier', 'cage', 'hutch', 'run', 'tank', 'aquarium', 'vivarium', 'viv', 'filter', 'heat lamp', 'toy', 'toys', 'bowl', 'bowls', 'feeder', 'fountain', 'litter tray', 'litter box', 'harness', 'collar', 'lead', 'leash', 'coat', 'kennel', 'flap', 'pen', 'playpen', 'gate', 'food', 'brush', 'stroller', 'backpack', 'perch'];

    private const SERVICE_WORDS = [
        'pet-services-dog-walking' => ['walker', 'walking', 'dog walks', 'walks'],
        'pet-services-pet-sitting' => ['sitter', 'sitting', 'pet sitter', 'drop in', 'house sit', 'feeding visits'],
        'pet-services-grooming' => ['groom', 'groomer', 'grooming', 'nail clip'],
        'pet-services-boarding-kennels-catteries' => ['boarding', 'cattery', 'kennels', 'daycare', 'day care', 'home boarding'],
        'pet-services-training-behaviour' => ['training', 'trainer', 'behaviour', 'behavior', 'behaviourist', 'puppy class', 'obedience'],
        'pet-services-pet-transport' => ['transport', 'courier', 'pet taxi', 'relocation'],
    ];

    private const PRODUCT_LEAF = [
        'cat' => 'pet-products-cat-supplies', 'dog' => 'pet-products-dog-supplies',
        'rabbit' => 'pet-products-rabbit-small-animal-supplies', 'small' => 'pet-products-rabbit-small-animal-supplies',
        'bird' => 'pet-products-bird-supplies', 'reptile' => 'pet-products-fish-reptile-supplies', 'fish' => 'pet-products-fish-reptile-supplies',
    ];

    public static function suggest(string $title, int $limit = 3): array
    {
        $text = ' '.Str::of($title)->lower()->replaceMatches('/[^a-z0-9 ]+/', ' ')->squish().' ';

        if (mb_strlen(trim($text)) < 3) {
            return [];
        }

        $species = self::species($text);
        $suggestions = [];

        if (self::has($text, ['lost', 'missing', 'escaped', 'stolen'])) {
            $suggestions[] = 'lost-and-found-lost-pets';
        }

        if (self::has($text, ['found', 'stray', 'turned up', 'wandered in'])) {
            $suggestions[] = 'lost-and-found-found-pets';
        }

        foreach (self::SERVICE_WORDS as $slug => $words) {
            if (self::has($text, $words)) {
                $suggestions[] = $slug;
            }
        }

        if (self::has($text, self::PRODUCT_WORDS) || (str_contains($text, ' litter ') && ! self::young($text))) {
            $suggestions[] = self::PRODUCT_LEAF[$species] ?? 'pet-products-other-pet-supplies';
        }

        if ($suggestions === []) {
            foreach (self::animalLeaves($text, $species) as $slug) {
                $suggestions[] = $slug;
            }
        }

        return array_slice(array_values(array_unique($suggestions)), 0, $limit);
    }

    private static function animalLeaves(string $text, ?string $species): array
    {
        if ($species === null) {
            return [];
        }

        $root = self::ROOTS[$species];
        $rehoming = self::has($text, ['rehome', 'rehoming', 'adopt', 'adoption', 'free to good home', 'needs a home', 'new home', 'forever home', 'bonded', 'senior', 'elderly']);
        $litter = self::has($text, ['upcoming litter', 'expected', 'due', 'pregnant', 'taking deposits', 'waiting list', 'reserve now']);
        $registered = self::has($text, ['pedigree', 'kc registered', 'kc reg', 'kennel club', 'gccf', 'tica', 'akc', 'registered', 'champion', 'bloodline']);
        $breed = self::mentionsBreed($text, $species);

        if (! in_array($species, ['cat', 'dog'], true)) {
            return $rehoming
                ? [$root.'-'.Str::slug(self::plural($species).' for rehoming'), $root.'-'.Str::slug(self::plural($species).' for sale')]
                : [$root.'-'.Str::slug(self::plural($species).' for sale'), $root.'-'.Str::slug(self::plural($species).' for rehoming')];
        }

        $young = $species === 'dog' ? 'dogs-puppies-for-sale' : 'cats-kittens-for-sale';
        $pedigreeLeaf = $species === 'dog' ? 'dogs-pedigree-dogs-puppies' : 'cats-pedigree-cats-kittens';
        $rehomingLeaf = $species === 'dog' ? 'dogs-dogs-for-rehoming' : 'cats-cats-for-rehoming';
        $litterLeaf = $root.'-upcoming-litters';

        return match (true) {
            $litter => [$litterLeaf, $pedigreeLeaf],
            $rehoming => [$rehomingLeaf],
            $registered => [$pedigreeLeaf, $young],
            self::young($text) => [$young, $pedigreeLeaf],
            $breed => [$pedigreeLeaf, $rehomingLeaf],
            default => [$rehomingLeaf, $pedigreeLeaf],
        };
    }

    private static function plural(string $species): string
    {
        return match ($species) {
            'rabbit' => 'Rabbits',
            'small' => 'Small animals',
            'bird' => 'Birds',
            'reptile' => 'Reptiles',
            'fish' => 'Fish',
            default => 'Other pets',
        };
    }

    private static function species(string $text): ?string
    {
        foreach (self::SPECIES_WORDS as $species => $words) {
            if (self::has($text, $words)) {
                return $species;
            }
        }

        foreach (['cat', 'dog', 'rabbit'] as $species) {
            if (self::mentionsBreed($text, $species)) {
                return $species;
            }
        }

        return null;
    }

    private static function young(string $text): bool
    {
        return self::has($text, ['kitten', 'kittens', 'puppy', 'puppies', 'pup', 'pups', 'litter']);
    }

    private static function mentionsBreed(string $text, string $species): bool
    {
        foreach (PetCatalog::breeds($species) as $breed) {
            $name = Str::of($breed)->lower()->before('(')->trim()->value();

            if (in_array($name, ['other', 'crossbreed', 'domestic shorthair', 'domestic longhair'], true)) {
                continue;
            }

            if (str_contains($text, ' '.$name.' ') || str_contains($text, ' '.$name.'s ')) {
                return true;
            }
        }

        $shorthands = [
            'cat' => '/ (bsh|blh|raggie|sphinx) /',
            'dog' => '/ (frenchie|frenchies|staffy|staffie|lab|labs|goldie|cockapoo|cavapoo|cavachon|pomsky|dachshund|sausage dog|jackapoo|maltipoo|shih tzu|gsd|husky|huskies) /',
        ];

        return isset($shorthands[$species]) && preg_match($shorthands[$species], $text) === 1;
    }

    private static function has(string $text, array $words): bool
    {
        foreach ($words as $word) {
            if (str_contains($text, ' '.$word.' ')) {
                return true;
            }
        }

        return false;
    }
}
