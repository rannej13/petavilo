<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class PainCheck
{
    public const RED = 'red';

    public const AMBER = 'amber';

    public const GREEN = 'green';

    public static function markers(): array
    {
        return [
            'ears' => ['label' => 'Ears', 'options' => ['Facing forwards, relaxed', 'Slightly pulled apart', 'Flattened and turned outwards']],
            'eyes' => ['label' => 'Eyes', 'options' => ['Open, round', 'Partly closed', 'Squinting, narrowed']],
            'muzzle' => ['label' => 'Muzzle (around nose and mouth)', 'options' => ['Relaxed, round', 'Slightly tense', 'Tense, pulled in, oval']],
            'whiskers' => ['label' => 'Whiskers', 'options' => ['Loose and curved', 'Slightly straight', 'Straight and pointing forwards']],
            'head' => ['label' => 'Head position', 'options' => ['Above the shoulders', 'Level with the shoulders', 'Below the shoulders or tilted down']],
        ];
    }

    public static function redFlags(): array
    {
        return [
            'straining' => 'Straining in the litter tray with little or no wee (especially male cats)',
            'breathing' => 'Breathing with an open mouth, panting, or struggling to breathe',
            'collapse' => 'Collapsed, can\'t stand, or a seizure',
            'injury' => 'Hit by a car, a fall, or bleeding that will not stop',
            'poison' => 'May have eaten or touched something poisonous, such as lilies or a dog flea treatment',
            'back_legs' => 'Sudden weak, cold or painful back legs',
        ];
    }

    public static function concerns(): array
    {
        return [
            'not_eating' => 'Not eating for more than 24 hours',
            'vomiting' => 'Vomiting more than twice in a day',
            'hiding' => 'Hiding, unusually quiet or not wanting to be touched',
            'drinking' => 'Drinking or weeing much more than usual',
        ];
    }

    public static function assess(array $scores, array $flags, array $concerns): array
    {
        $total = array_sum(array_map(static fn (mixed $value): int => max(0, min(2, (int) $value)), $scores));
        $redFlags = array_values(array_intersect_key(self::redFlags(), array_flip($flags)));
        $otherConcerns = array_values(array_intersect_key(self::concerns(), array_flip($concerns)));

        if ($redFlags !== []) {
            $tier = self::RED;
        } elseif ($total >= 4 || $otherConcerns !== []) {
            $tier = self::AMBER;
        } else {
            $tier = self::GREEN;
        }

        return [
            'tier' => $tier,
            'score' => $total,
            'scored' => count($scores),
            'red_flags' => $redFlags,
            'concerns' => $otherConcerns,
            'title' => match ($tier) {
                self::RED => 'Emergency: call a vet now',
                self::AMBER => 'See a vet today or tomorrow',
                default => 'No urgent signs from these answers',
            },
            'advice' => match ($tier) {
                self::RED => 'What you described can be life-threatening. Call your vet or the nearest out-of-hours emergency vet straight away and tell them you are on your way. A male cat that cannot wee can die within a day or two.',
                self::AMBER => $total >= 4
                    ? 'A score of '.$total.' out of 10 suggests your cat may be in pain. Book a vet appointment as soon as you can, ideally today, and call sooner if things get worse.'
                    : 'These signs need a vet check soon. Book an appointment and call sooner if anything gets worse.',
                default => 'Keep an eye on your cat and check again later. Cats hide pain well, so trust your instincts and call your vet if you are worried.',
            },
        ];
    }
}
