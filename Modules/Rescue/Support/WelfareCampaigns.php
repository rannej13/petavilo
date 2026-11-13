<?php

declare(strict_types=1);

namespace Modules\Rescue\Support;

final class WelfareCampaigns
{
    public static function all(): array
    {
        return [
            'helping-hand' => [
                'question' => 'If a hungry or injured animal came to your door, would you offer a helping hand?',
                'answers' => [
                    'yes-straight-away' => 'Yes, straight away',
                    'yes-if-i-knew-how' => 'Yes, if I knew what to do',
                    'call-someone' => 'I would call someone who can help',
                    'not-sure' => 'Not sure',
                ],
                'actions' => [
                    ['title' => 'Keep yourself safe first', 'text' => 'Frightened or hurt animals can bite or scratch, even friendly ones. Do not corner them or pick them up if they are resisting.'],
                    ['title' => 'Injured or very unwell? Call for help', 'text' => 'Ring a local vet or the animal welfare line for your area. Most vets will scan for a microchip for free.'],
                    ['title' => 'Hungry but well', 'text' => 'Fresh water is always fine. A small amount of plain cooked chicken or pet food is safer than milk or leftovers. Do not keep feeding a cat that may belong to someone without checking first.'],
                    ['title' => 'Check for an owner', 'text' => 'Look for a collar tag, ask neighbours and post a free Found advert. Many cats roam and are not lost.'],
                    ['title' => 'Want to do more?', 'text' => 'Verified rescues near you need fosterers, volunteers and donations of food and bedding.'],
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }
}
