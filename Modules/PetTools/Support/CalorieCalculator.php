<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class CalorieCalculator
{
    public static function factors(): array
    {
        return [
            'cat' => [
                'loss' => ['label' => 'Needs to lose weight', 'factor' => 0.8],
                'indoor' => ['label' => 'Neutered adult, mostly indoors', 'factor' => 1.0],
                'adult' => ['label' => 'Neutered adult with outdoor access', 'factor' => 1.2],
                'entire' => ['label' => 'Adult, not neutered', 'factor' => 1.4],
                'kitten' => ['label' => 'Kitten under 12 months', 'factor' => 2.5],
            ],
            'dog' => [
                'loss' => ['label' => 'Needs to lose weight', 'factor' => 1.0],
                'inactive' => ['label' => 'Older or not very active', 'factor' => 1.4],
                'adult' => ['label' => 'Neutered adult, normal activity', 'factor' => 1.6],
                'entire' => ['label' => 'Adult, not neutered', 'factor' => 1.8],
                'working' => ['label' => 'Working or very active', 'factor' => 2.5],
                'puppy_young' => ['label' => 'Puppy under 4 months', 'factor' => 3.0],
                'puppy' => ['label' => 'Puppy 4 to 12 months', 'factor' => 2.0],
            ],
        ];
    }

    public static function textures(): array
    {
        return ['mousse' => 'Mousse or smooth pâté', 'pate' => 'Firm pâté or loaf', 'chunks' => 'Chunks in gravy', 'jelly' => 'Chunks in jelly', 'shreds' => 'Shreds or flakes in broth', 'dry' => 'Dry biscuits'];
    }

    public static function calculate(string $species, float $weightKg, string $stage, float $kcalPer100g, float $oldKcalPer100g): ?array
    {
        $factor = self::factors()[$species][$stage]['factor'] ?? null;

        if ($factor === null || $weightKg <= 0 || $weightKg > 100) {
            return null;
        }

        $rer = 70 * ($weightKg ** 0.75);
        $daily = $rer * $factor;
        $newDensity = $kcalPer100g > 0 ? $kcalPer100g : 0.0;
        $oldDensity = $oldKcalPer100g > 0 ? $oldKcalPer100g : $newDensity;

        return [
            'rer' => (int) round($rer),
            'kcal' => (int) round($daily),
            'factor' => $factor,
            'grams' => $newDensity > 0 ? (int) round($daily / $newDensity * 100) : null,
            'schedule' => $newDensity > 0 ? self::schedule($daily, $oldDensity, $newDensity) : [],
        ];
    }

    public static function alternatives(string $rejected): array
    {
        return match ($rejected) {
            'mousse' => ['pate', 'shreds'],
            'pate' => ['mousse', 'shreds', 'chunks'],
            'chunks' => ['jelly', 'shreds', 'pate'],
            'jelly' => ['chunks', 'shreds'],
            'shreds' => ['chunks', 'pate'],
            'dry' => ['pate', 'chunks'],
            default => ['pate', 'chunks', 'shreds'],
        };
    }

    private static function schedule(float $daily, float $oldDensity, float $newDensity): array
    {
        $rows = [];

        foreach ([['Days 1 to 3', 75], ['Days 4 to 7', 50], ['Days 8 to 10', 25], ['Days 11 to 14', 0]] as [$label, $oldPercent]) {
            $oldKcal = $daily * $oldPercent / 100;
            $rows[] = [
                'label' => $label,
                'old_percent' => $oldPercent,
                'old_grams' => (int) round($oldKcal / $oldDensity * 100),
                'new_grams' => (int) round(($daily - $oldKcal) / $newDensity * 100),
            ];
        }

        return $rows;
    }
}
