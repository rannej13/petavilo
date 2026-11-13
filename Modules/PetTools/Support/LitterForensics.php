<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class LitterForensics
{
    public static function questions(): array
    {
        return [
            'medical' => ['label' => 'Is your cat straining, crying in the tray, weeing tiny amounts often, or is there blood in the wee?', 'options' => ['no' => 'No', 'yes' => 'Yes']],
            'cats' => ['label' => 'How many cats live with you?', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4 or more']],
            'boxes' => ['label' => 'How many litter trays do you have?', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5 or more']],
            'hood' => ['label' => 'Are the trays covered (hooded)?', 'options' => ['no' => 'No, open', 'some' => 'Some are', 'yes' => 'Yes, all of them']],
            'location' => ['label' => 'Where is the main tray?', 'options' => ['quiet' => 'A quiet spot with two ways out', 'busy' => 'A busy spot (hallway, kitchen)', 'food' => 'Near food or water bowls', 'trapped' => 'In a corner, cupboard or room with one way out', 'noisy' => 'Next to a washing machine or boiler']],
            'litter' => ['label' => 'What litter do you use?', 'options' => ['clumping' => 'Unscented clumping', 'scented' => 'Scented', 'pellets' => 'Wood or paper pellets', 'crystals' => 'Silica crystals', 'changed' => 'We changed litter recently']],
            'scoop' => ['label' => 'How often is the tray scooped?', 'options' => ['daily' => 'At least once a day', 'often' => 'Every 2 to 3 days', 'rarely' => 'Less often']],
            'where' => ['label' => 'Where are the accidents?', 'options' => ['vertical' => 'Sprayed up walls, doors or furniture', 'soft' => 'On soft things (beds, rugs, laundry)', 'beside' => 'Right next to the tray', 'hard' => 'On hard floors, bath or sink', 'poo' => 'Only poo outside the tray']],
            'age' => ['label' => 'How old is your cat?', 'options' => ['young' => 'Under 10', 'senior' => '10 or older']],
            'changes' => ['label' => 'Any big changes in the last few months?', 'options' => ['no' => 'No', 'yes' => 'Yes: new pet, baby, move, building work or a cat outside']],
        ];
    }

    public static function diagnose(array $answers): array
    {
        if (($answers['medical'] ?? 'no') === 'yes') {
            return ['emergency' => true, 'findings' => [], 'shopping' => []];
        }

        $findings = [];
        $shopping = [];
        $cats = (int) ($answers['cats'] ?? 1);
        $boxes = (int) ($answers['boxes'] ?? 1);

        if ($boxes < $cats + 1) {
            $findings[] = ['title' => 'Not enough trays', 'text' => 'Vets recommend one tray per cat plus one extra, in different places. With '.$cats.' '.($cats === 1 ? 'cat' : 'cats').' you need '.($cats + 1).'. You have '.$boxes.'.'];
            $shopping[] = 'large open cat litter tray';
        }

        if (in_array($answers['hood'] ?? 'no', ['some', 'yes'], true)) {
            $findings[] = ['title' => 'Covered trays', 'text' => 'Hoods trap smells and can leave a cat feeling cornered. Try taking the lid off at least one tray.'];
        }

        $location = $answers['location'] ?? 'quiet';

        if ($location !== 'quiet') {
            $findings[] = ['title' => 'Tray location', 'text' => match ($location) {
                'busy' => 'Busy spots get interrupted. Move a tray somewhere quieter.',
                'food' => 'Cats do not like toileting near where they eat. Keep food and water well away from trays.',
                'trapped' => 'A tray with only one way out is easy to ambush, especially with other pets. Put it where your cat can see around and leave two ways.',
                default => 'Sudden noises from machines can frighten a cat off the tray. Move it somewhere calmer.',
            }];
        }

        $litter = $answers['litter'] ?? 'clumping';

        if ($litter !== 'clumping') {
            $findings[] = ['title' => 'Litter type', 'text' => match ($litter) {
                'scented' => 'Most cats prefer unscented litter. Scents that smell clean to us can be strong to them.',
                'pellets', 'crystals' => 'Many cats dislike hard pellets or crystals underfoot. Fine, soft, unscented clumping litter suits most.',
                default => 'A sudden litter change is a common cause. Go back to the old litter, or mix in the new one gradually.',
            }];
            $shopping[] = 'unscented clumping cat litter';
        }

        if (($answers['scoop'] ?? 'daily') !== 'daily') {
            $findings[] = ['title' => 'Cleaning', 'text' => 'Scoop at least once a day and wash the tray with warm water every week or two. Avoid strong-smelling disinfectants.'];
        }

        $where = $answers['where'] ?? '';
        $diagnosis = match ($where) {
            'vertical' => ['title' => 'Likely: marking', 'text' => 'Spraying up vertical surfaces is usually marking, often from stress or other cats. Neutering helps, and so do more resources (beds, bowls, trays) and blocking views of cats outside. Ask your vet about calming support.'],
            'soft' => ['title' => 'Likely: pain or surface preference', 'text' => 'Weeing on soft things can mean it hurts to wee and the tray feels linked to pain. Book a vet check to rule out cystitis or infection.'],
            'beside' => ['title' => 'Likely: the tray itself', 'text' => 'Going right next to the tray means your cat wants to use it but dislikes something about it: size, hood, litter or cleanliness.'],
            'hard' => ['title' => 'Likely: litter dislike or pain', 'text' => 'Choosing cool smooth surfaces can mean a dislike of the litter, or discomfort. A vet check is worthwhile.'],
            'poo' => ['title' => 'Likely: tray or digestion', 'text' => 'Pooing outside can come from a small or dirty tray, constipation or joint pain. Mention it at your next vet visit.'],
            default => null,
        };

        if ($diagnosis !== null) {
            $findings[] = $diagnosis;
        }

        if (($answers['age'] ?? 'young') === 'senior') {
            $findings[] = ['title' => 'Older joints', 'text' => 'Older cats often have arthritis and find high sides hard. Use a tray with a low entry and ask your vet about joint pain.'];
            $shopping[] = 'low entry cat litter tray senior';
        }

        if (($answers['changes'] ?? 'no') === 'yes') {
            $findings[] = ['title' => 'Stress', 'text' => 'Changes at home are a common trigger. Keep routines steady, give your cat safe high places to retreat to, and add an extra tray.'];
        }

        if (in_array($where, ['vertical', 'soft', 'beside', 'hard', 'poo'], true)) {
            $shopping[] = 'enzyme cleaner cat urine';
        }

        return ['emergency' => false, 'findings' => $findings, 'shopping' => array_values(array_unique($shopping))];
    }
}
