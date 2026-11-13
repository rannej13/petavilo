<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class ClinicTriage
{
    public static function questions(): array
    {
        return [
            'species' => ['label' => 'Pet', 'options' => ['cat' => 'Cat', 'dog' => 'Dog', 'other' => 'Other']],
            'sex' => ['label' => 'Sex', 'options' => ['male_neutered' => 'Male, neutered', 'male' => 'Male, not neutered', 'female_spayed' => 'Female, spayed', 'female' => 'Female, not spayed', 'unknown' => 'Not sure']],
            'complaint' => ['label' => 'Main problem', 'options' => [
                'vomiting' => 'Vomiting or diarrhoea', 'urinary' => 'Toileting problem or straining', 'not_eating' => 'Not eating',
                'breathing' => 'Breathing problem', 'injury' => 'Injury or limping', 'lethargy' => 'Lethargic or hiding',
                'skin' => 'Skin, ears or eyes', 'poison' => 'May have eaten something harmful', 'other' => 'Something else',
            ]],
            'duration' => ['label' => 'How long?', 'options' => ['hours' => 'Started in the last few hours', 'day' => 'About a day', 'days' => '2 to 3 days', 'week' => 'A week or more']],
            'appetite' => ['label' => 'Eating', 'options' => ['normal' => 'Normal', 'less' => 'Less than usual', 'none' => 'Nothing for 24 hours or more']],
            'water' => ['label' => 'Drinking', 'options' => ['normal' => 'Normal', 'more' => 'More than usual', 'less' => 'Less than usual']],
            'urine' => ['label' => 'Weeing', 'options' => ['normal' => 'Normal', 'small' => 'Smaller amounts or clumps', 'straining' => 'Straining with little or nothing coming out', 'blood' => 'Blood in the wee']],
            'vomit_count' => ['label' => 'Vomiting in the last 24 hours', 'options' => ['0' => 'None', '1' => 'Once or twice', '3' => 'Three times or more', 'blood' => 'With blood']],
            'breathing' => ['label' => 'Breathing', 'options' => ['normal' => 'Normal', 'fast' => 'Fast or noisy', 'open_mouth' => 'Open mouth, panting (cat) or struggling']],
            'alert' => ['label' => 'Alertness', 'options' => ['normal' => 'Normal', 'quiet' => 'Quieter than usual', 'collapsed' => 'Collapsed, wobbly or fitting']],
        ];
    }

    public static function assess(array $answers, int $painScore): array
    {
        $reasons = [];
        $red = false;
        $amber = false;

        $male = str_starts_with((string) ($answers['sex'] ?? ''), 'male');

        if (($answers['urine'] ?? '') === 'straining') {
            $red = true;
            $reasons[] = $male ? 'Straining with little or no urine in a male animal: possible urethral obstruction' : 'Straining with little or no urine';
        }

        if (($answers['breathing'] ?? '') === 'open_mouth') {
            $red = true;
            $reasons[] = 'Respiratory distress reported';
        }

        if (($answers['alert'] ?? '') === 'collapsed') {
            $red = true;
            $reasons[] = 'Collapse, ataxia or seizure reported';
        }

        if (($answers['complaint'] ?? '') === 'poison') {
            $red = true;
            $reasons[] = 'Possible toxin exposure';
        }

        if (($answers['vomit_count'] ?? '') === 'blood') {
            $red = true;
            $reasons[] = 'Haematemesis reported';
        }

        if ($painScore >= 4) {
            $amber = true;
            $reasons[] = 'Owner-scored grimace scale '.$painScore.'/10';
        }

        foreach ([
            ['appetite', 'none', 'Anorexia 24 hours or more'],
            ['vomit_count', '3', 'Vomiting three or more times in 24 hours'],
            ['urine', 'blood', 'Haematuria reported'],
            ['urine', 'small', 'Reduced urine output reported'],
            ['breathing', 'fast', 'Tachypnoea or noisy breathing reported'],
            ['water', 'more', 'Polydipsia reported'],
        ] as [$field, $value, $reason]) {
            if (($answers[$field] ?? '') === $value) {
                $amber = true;
                $reasons[] = $reason;
            }
        }

        $tier = $red ? 'red' : ($amber ? 'amber' : 'green');

        return ['tier' => $tier, 'reasons' => $reasons];
    }

    public static function soap(array $answers, array $pain, array $assessment, string $petName, string $age): string
    {
        $q = self::questions();
        $label = static fn (string $key): string => $q[$key]['options'][$answers[$key] ?? ''] ?? 'Not given';
        $tierText = ['red' => 'RED (immediate)', 'amber' => 'AMBER (same-day exam advised)', 'green' => 'GREEN (routine)'][$assessment['tier']];
        $painLine = $pain['scored'] > 0 ? 'Owner-scored Feline Grimace Scale: '.$pain['score'].'/'.($pain['scored'] * 2).' ('.$pain['scored'].' of 5 action units scored)' : 'Grimace scale not scored';

        return implode("\n", [
            '[SUBJECTIVE]',
            'Patient: '.($petName !== '' ? $petName : 'Not named').', '.$label('species').', '.$label('sex').', age '.($age !== '' ? $age : 'not given').'.',
            'Presenting complaint: '.$label('complaint').'. Duration: '.$label('duration').'.',
            'Appetite: '.$label('appetite').'. Water intake: '.$label('water').'.',
            'Owner notes: '.(trim((string) ($answers['notes'] ?? '')) ?: 'None'),
            '',
            '[OBJECTIVE (owner-reported, not examined)]',
            $painLine.'.',
            'Urination: '.$label('urine').'. Vomiting: '.$label('vomit_count').'.',
            'Breathing: '.$label('breathing').'. Mentation: '.$label('alert').'.',
            '',
            '[ASSESSMENT]',
            'Triage tier: '.$tierText.'.',
            $assessment['reasons'] === [] ? 'No red-flag answers.' : 'Flags: '.implode('; ', $assessment['reasons']).'.',
            '',
            '[PLAN]',
            match ($assessment['tier']) {
                'red' => 'Owner advised to seek emergency veterinary care immediately.',
                'amber' => 'Owner advised to book a same-day examination.',
                default => 'Owner advised to book a routine appointment and to call if signs worsen.',
            },
            'Generated from owner answers by an automated triage form. Not a clinical examination or diagnosis.',
        ]);
    }
}
