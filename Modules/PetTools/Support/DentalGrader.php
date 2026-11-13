<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class DentalGrader
{
    public static function gumStages(): array
    {
        return [
            1 => 'Clean white teeth, pink gums',
            2 => 'A thin red line along the gums, a little yellow staining',
            3 => 'Brown tartar on the back teeth, red or puffy gums',
            4 => 'Thick tartar, gums pulled back, wobbly or missing teeth, or a strong smell',
        ];
    }

    public static function signs(): array
    {
        return [
            'dropping' => 'Drops food or chews on one side',
            'chattering' => 'Jaw chattering or pawing at the mouth',
            'breath' => 'Bad breath',
            'drooling' => 'Drooling or blood on toys',
        ];
    }

    public static function seniorSigns(): array
    {
        return [
            'drinking' => 'Drinking or weeing more than before',
            'weight' => 'Losing weight without trying',
            'appetite' => 'Eating less or fussier than before',
            'coat' => 'Coat looks scruffy or unkempt',
        ];
    }

    public static function grade(int $gums, array $signs, string $age, array $senior): array
    {
        $gums = max(1, min(4, $gums));
        $signCount = count(array_intersect(array_keys(self::signs()), $signs));
        $stage = min(4, $gums + ($signCount >= 2 && $gums < 4 ? 1 : 0));
        $isSenior = in_array($age, ['10-14', '15+'], true);
        $seniorCount = count(array_intersect(array_keys(self::seniorSigns()), $senior));

        return [
            'stage' => $stage,
            'stage_text' => match ($stage) {
                1 => 'Looks healthy. Keep up regular checks and tooth brushing if your pet allows it.',
                2 => 'Early gum inflammation. Often reversible with a vet clean and home care.',
                3 => 'Likely gum disease. A vet dental check under anaesthetic is usually needed.',
                default => 'Advanced dental disease is likely. This is painful; book a vet check soon.',
            },
            'dental_check' => $stage >= 2 || $signCount > 0,
            'senior_check' => $isSenior || $seniorCount > 0,
            'senior_urgent' => $seniorCount >= 2,
            'senior_text' => $seniorCount >= 2
                ? 'Drinking more, weight loss and appetite changes together can be signs of kidney disease, an overactive thyroid or diabetes, all common in older cats. Ask your vet for blood and urine tests soon.'
                : ($isSenior ? 'Cats over 10 benefit from a yearly senior check with blood pressure, blood and urine tests. Kidney disease caught early is easier to manage.' : null),
        ];
    }
}
