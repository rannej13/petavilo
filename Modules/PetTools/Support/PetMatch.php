<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

/**
 * "Is this pet right for me?" Answers use the same wording as rescue
 * application forms so a saved result can pre-fill an application.
 */
final class PetMatch
{
    public static function questions(): array
    {
        return [
            'hours_alone' => ['label' => 'On a normal day, how long would the pet be on its own?', 'options' => ['Under 2', '2 to 4', '4 to 6', 'Over 6']],
            'home_type' => ['label' => 'Your home', 'options' => ['House with garden', 'House without garden', 'Flat with outdoor space', 'Flat without outdoor space', 'Other']],
            'tenure' => ['label' => 'Do you own or rent?', 'options' => ['Own', 'Rent, pets allowed in writing', 'Rent, need to ask the landlord', 'Live with family']],
            'budget' => ['label' => 'What could you spend each month on food, insurance and care?', 'options' => ['Under £50', '£50 to £100', '£100 to £150', 'Over £150']],
            'activity' => ['label' => 'How much time could you give to walks, play or exercise each day?', 'options' => ['Under 30 minutes', '30 to 60 minutes', 'Over an hour']],
            'children' => ['label' => 'Children at home', 'options' => ['None', 'Under 5', '5 to 11', '12 and over', 'Visit often']],
            'other_pets' => ['label' => 'Other pets at home', 'options' => ['None', 'Cats', 'Dogs', 'Cats and dogs', 'Other pets']],
            'allergies' => ['label' => 'Allergies to animals in the household', 'options' => ['None', 'Mild', 'Severe or not sure']],
            'experience' => ['label' => 'Have you looked after a pet before?', 'options' => ['First time', 'Some', 'Lots']],
            'years' => ['label' => 'How many years ahead can you plan with a pet in your life?', 'options' => ['Up to 5', '5 to 10', '10 to 15', '15 or more']],
            'contact' => ['label' => 'How hands-on do you want to be?', 'options' => ['Lots of cuddles', 'Some handling', 'Happy to watch']],
        ];
    }

    /**
     * Shared with AdoptionApplication::questions().
     */
    public static function applicationKeys(): array
    {
        return ['home_type', 'tenure', 'children', 'other_pets', 'hours_alone', 'experience'];
    }

    public static function pets(): array
    {
        return [
            'adult-cat' => ['label' => 'An adult cat', 'species' => 'cat', 'lifespan' => '12 to 18 years', 'cost' => 'cat', 'adopt' => 'cat'],
            'kitten' => ['label' => 'A kitten', 'species' => 'cat', 'lifespan' => '12 to 18 years', 'cost' => 'cat', 'adopt' => 'cat'],
            'adult-dog' => ['label' => 'An adult dog', 'species' => 'dog', 'lifespan' => '10 to 13 years', 'cost' => 'dog', 'adopt' => 'dog'],
            'puppy' => ['label' => 'A puppy', 'species' => 'dog', 'lifespan' => '10 to 13 years', 'cost' => 'dog', 'adopt' => 'dog'],
            'rabbits' => ['label' => 'A pair of rabbits', 'species' => 'rabbit', 'lifespan' => '8 to 12 years', 'cost' => 'rabbit', 'adopt' => 'rabbit'],
            'guinea-pigs' => ['label' => 'A pair of guinea pigs', 'species' => 'guinea-pig', 'lifespan' => '5 to 8 years', 'cost' => null, 'adopt' => 'small'],
            'fish' => ['label' => 'A fish tank', 'species' => 'fish', 'lifespan' => 'depends on the fish', 'cost' => null, 'adopt' => 'fish'],
        ];
    }

    /**
     * Published figures, shown with their source.
     */
    public static function costs(): array
    {
        return [
            'dog' => ['line' => 'Owners report spending about £96 a month on a dog. PDSA puts the lifetime cost at £4,600 to £30,800 depending on size.', 'source' => 'Petplan owner survey; PDSA via Ageas'],
            'cat' => ['line' => 'Owners report spending about £70 a month on a cat. PDSA says most owners spend at least £12,000 over a cat\'s life.', 'source' => 'Petplan owner survey; PDSA via Ageas'],
            'rabbit' => ['line' => 'Rabbit owners report spending about £82 a month. PDSA puts a pair at £6,500 to £9,000 over their lives.', 'source' => 'Petplan owner survey; PDSA via Ageas'],
        ];
    }

    public static function sources(): array
    {
        return [
            ['label' => 'Petplan: Furry firsts owner survey', 'url' => 'https://www.petplan.co.uk/pet-insurance/pet-care-and-advice/furry-firsts.html'],
            ['label' => 'Ageas, quoting PDSA lifetime costs', 'url' => 'https://www.ageas.co.uk/link/4239250c6b5a48dbb110adbf34eb2eb0.aspx'],
        ];
    }

    /**
     * @param  array<string, string>  $answers
     * @return list<array{key: string, label: string, score: int, fit: string, good: list<string>, watch: list<string>, lifespan: string, cost: ?array, adopt: string}>
     */
    public static function assess(array $answers): array
    {
        $a = static fn (string $key): string => (string) ($answers[$key] ?? '');
        $alone = $a('hours_alone');
        $longAlone = in_array($alone, ['4 to 6', 'Over 6'], true);
        $flatNoOutside = $a('home_type') === 'Flat without outdoor space';
        $flat = str_starts_with($a('home_type'), 'Flat');
        $lowBudget = $a('budget') === 'Under £50';
        $lowActivity = $a('activity') === 'Under 30 minutes';
        $highActivity = $a('activity') === 'Over an hour';
        $smallKids = $a('children') === 'Under 5';
        $severeAllergy = $a('allergies') === 'Severe or not sure';
        $mildAllergy = $a('allergies') === 'Mild';
        $firstTime = $a('experience') === 'First time';
        $years = $a('years');
        $cuddles = $a('contact') === 'Lots of cuddles';
        $watcher = $a('contact') === 'Happy to watch';
        $hasDogs = in_array($a('other_pets'), ['Dogs', 'Cats and dogs'], true);
        $hasCats = in_array($a('other_pets'), ['Cats', 'Cats and dogs'], true);

        $results = [];

        foreach (self::pets() as $key => $pet) {
            $score = 5;
            $good = [];
            $watch = [];
            $add = static function (int $points, string $reason) use (&$score, &$good, &$watch): void {
                $score += $points;

                if ($points > 0) {
                    $good[] = $reason;
                } else {
                    $watch[] = $reason;
                }
            };

            switch ($key) {
                case 'puppy':
                    $alone === 'Under 2' ? $add(2, 'Someone is home most of the day, which puppies need') : $add(-4, 'Puppies should not be left alone for more than an hour or two while young');
                    $highActivity ? $add(2, 'You have time for daily exercise and training') : ($lowActivity ? $add(-3, 'Dogs need daily walks and play; under 30 minutes is too little for most') : null);
                    $firstTime ? $add(-1, 'Puppy training is hard work for a first-time owner; a training class helps a lot') : null;
                    $flatNoOutside ? $add(-2, 'Toilet training is harder with no outdoor space') : null;
                    in_array($years, ['Up to 5', '5 to 10'], true) ? $add(-3, 'Dogs usually live 10 to 13 years') : null;
                    $lowBudget ? $add(-2, 'A dog usually costs more than £50 a month') : null;
                    $severeAllergy ? $add(-4, 'Dog allergies are common and no breed is truly allergy-free') : null;
                    break;
                case 'adult-dog':
                    $alone === 'Over 6' ? $add(-4, 'Most dogs should not be left alone for more than about 4 hours') : ($alone === '4 to 6' ? $add(-2, 'Most dogs should not be left alone for more than about 4 hours; a dog walker could help') : $add(1, 'Your hours suit a dog'));
                    $highActivity ? $add(2, 'You have time for daily walks') : ($lowActivity ? $add(-3, 'Dogs need daily walks; under 30 minutes is too little for most') : null);
                    $firstTime ? $add(1, 'An adult rescue dog with a known personality is easier than a puppy for a first dog') : null;
                    $years === 'Up to 5' ? $add(-1, 'Even an older dog may live another 5 years or more') : null;
                    $lowBudget ? $add(-2, 'A dog usually costs more than £50 a month') : null;
                    $severeAllergy ? $add(-4, 'Dog allergies are common and no breed is truly allergy-free') : null;
                    $hasCats ? $add(-1, 'Ask the rescue for a dog known to live with cats') : null;
                    break;
                case 'kitten':
                    $longAlone ? $add(-1, 'Kittens get into mischief alone; two kittens keep each other company') : $add(1, 'Someone is around for a young kitten');
                    in_array($years, ['Up to 5', '5 to 10', '10 to 15'], true) ? $add(-2, 'Cats often live 15 years or more') : $add(1, 'You can plan for a long-lived pet');
                    $smallKids ? $add(-1, 'Kittens scratch and need gentle handling, which is hard for under-5s') : null;
                    $severeAllergy ? $add(-5, 'Cat allergies are common and can be severe') : ($mildAllergy ? $add(-2, 'Spend time with cats before deciding: mild allergies can get worse') : null);
                    $lowBudget ? $add(-1, 'A cat usually costs about £70 a month') : null;
                    break;
                case 'adult-cat':
                    $longAlone ? $add(2, 'Adult cats cope well with time alone') : $add(1, 'Your hours suit a cat');
                    $flat ? $add(1, 'Many adult rescue cats are happy indoors; ask for an indoor cat') : null;
                    $firstTime ? $add(1, 'A settled adult cat is a good first pet') : null;
                    in_array($years, ['Up to 5', '5 to 10'], true) ? $add(-1, 'Even an older cat may live another 5 to 10 years') : null;
                    $severeAllergy ? $add(-5, 'Cat allergies are common and can be severe') : ($mildAllergy ? $add(-2, 'Spend time with cats before deciding: mild allergies can get worse') : null);
                    $hasDogs ? $add(-1, 'Ask the rescue for a cat known to live with dogs') : null;
                    $lowBudget ? $add(-1, 'A cat usually costs about £70 a month') : null;
                    break;
                case 'rabbits':
                    $cuddles ? $add(-2, 'Most rabbits dislike being picked up and cuddled') : ($watcher ? $add(1, 'Rabbits are lovely to watch and interact with on the floor') : null);
                    $smallKids ? $add(-2, 'Rabbits are easily hurt and scared by young children') : null;
                    $flatNoOutside ? $add(-1, 'House rabbits need a large, rabbit-proofed room to run in') : $add(1, 'You have room for a run');
                    $years === 'Up to 5' ? $add(-2, 'Rabbits usually live 8 to 12 years') : null;
                    $lowBudget ? $add(-1, 'Rabbit owners report spending about £82 a month') : null;
                    $severeAllergy ? $add(-3, 'Rabbit and hay allergies are common') : null;
                    $longAlone ? $add(1, 'A bonded pair keeps each other company while you are out') : null;
                    break;
                case 'guinea-pigs':
                    in_array($a('children'), ['5 to 11', '12 and over'], true) ? $add(1, 'Guinea pigs suit older children with an adult in charge') : null;
                    $smallKids ? $add(-1, 'Guinea pigs need gentle, supervised handling') : null;
                    in_array($years, ['Up to 5', '5 to 10'], true) ? $add(1, 'Their 5 to 8 year lifespan fits your plans') : null;
                    $longAlone ? $add(1, 'A pair keeps each other company while you are out') : null;
                    $severeAllergy ? $add(-3, 'Guinea pig and hay allergies are common') : null;
                    $cuddles ? $add(-1, 'Guinea pigs can be handled but are easily frightened') : null;
                    break;
                case 'fish':
                    $watcher ? $add(3, 'You are happy to watch rather than handle') : ($cuddles ? $add(-4, 'Fish can\'t be cuddled') : null);
                    $severeAllergy ? $add(2, 'No fur, so allergies are rarely a problem') : null;
                    $longAlone ? $add(1, 'Fish cope with you being out all day') : null;
                    $lowBudget ? $add(1, 'Running costs are low once the tank is set up') : null;
                    break;
            }

            $good = array_values(array_filter($good));
            $watch = array_values(array_filter($watch));
            $score = max(0, min(10, $score));

            $results[] = [
                'key' => $key,
                'label' => $pet['label'],
                'score' => $score,
                'fit' => match (true) {
                    $score >= 8 => 'Great fit',
                    $score >= 6 => 'Good fit',
                    $score >= 4 => 'Harder',
                    default => 'Not right now',
                },
                'good' => $good,
                'watch' => $watch,
                'lifespan' => $pet['lifespan'],
                'cost' => $pet['cost'] === null ? null : self::costs()[$pet['cost']],
                'adopt' => $pet['adopt'],
            ];
        }

        usort($results, static fn (array $x, array $y): int => $y['score'] <=> $x['score']);

        return $results;
    }

    /**
     * Warnings that apply whatever the pet.
     *
     * @param  array<string, string>  $answers
     * @return list<string>
     */
    public static function checks(array $answers): array
    {
        $checks = [];

        if (($answers['tenure'] ?? '') === 'Rent, need to ask the landlord') {
            $checks[] = 'Ask your landlord in writing before you apply. Rescues will ask to see it.';
        }

        if (($answers['tenure'] ?? '') === 'Live with family') {
            $checks[] = 'Make sure everyone you live with agrees, as they will share the care.';
        }

        if (($answers['budget'] ?? '') === 'Under £50') {
            $checks[] = 'Set aside money for vet bills or insurance from day one: one emergency can cost hundreds of pounds.';
        }

        $checks[] = 'Adopting from a rescue costs less up front and the animal\'s personality is already known.';

        return $checks;
    }
}
