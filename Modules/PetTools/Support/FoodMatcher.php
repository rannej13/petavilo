<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class FoodMatcher
{
    public static function questions(): array
    {
        return [
            'species' => ['label' => 'Your pet', 'options' => ['cat' => 'Cat', 'dog' => 'Dog', 'rabbit' => 'Rabbit', 'guinea-pig' => 'Guinea pig']],
            'age' => ['label' => 'Age', 'options' => ['young' => 'Kitten, puppy or baby', 'adult' => 'Adult', 'senior' => 'Senior']],
            'size' => ['label' => 'Size (dogs)', 'options' => ['small' => 'Small or toy', 'medium' => 'Medium', 'large' => 'Large or giant']],
            'activity' => ['label' => 'How active', 'options' => ['low' => 'Mostly indoors or relaxed', 'normal' => 'Normal', 'high' => 'Very active or working']],
            'weight' => ['label' => 'Body shape', 'options' => ['under' => 'A bit thin', 'ideal' => 'Just right', 'over' => 'Carrying extra weight']],
            'tummy' => ['label' => 'Sensitive tummy or itchy skin', 'options' => ['no' => 'No', 'yes' => 'Yes']],
            'style' => ['label' => 'Food style you prefer', 'options' => ['wet' => 'Wet', 'dry' => 'Dry', 'mixed' => 'A mix']],
        ];
    }

    public static function match(array $answers, string $country): array
    {
        $species = (string) ($answers['species'] ?? 'cat');
        $age = (string) ($answers['age'] ?? 'adult');

        return match ($species) {
            'dog' => self::dog($answers, $age, $country),
            'rabbit' => self::rabbit($age, $country),
            'guinea-pig' => self::guineaPig($country),
            default => self::cat($answers, $age, $country),
        };
    }

    private static function cat(array $answers, string $age, string $country): array
    {
        $stage = match ($age) {
            'young' => 'kitten',
            'senior' => 'senior',
            default => 'adult',
        };
        $style = (string) ($answers['style'] ?? 'mixed');
        $points = [
            'Choose a food labelled "complete" for '.$stage.' cats. "Complementary" foods and treats are not a full diet.',
            $style === 'dry'
                ? 'Cats get much of their water from food. With dry food, add a water fountain or a little wet food each day.'
                : 'Wet food helps cats drink enough, which supports their kidneys and bladder.',
            'Cats need meat. Avoid vegetarian or vegan diets.',
        ];

        if ($stage === 'kitten') {
            $points[] = 'Kittens need small meals 3 to 4 times a day until about 6 months old.';
        }

        if ($stage === 'senior') {
            $points[] = 'Older cats benefit from a yearly vet check, including kidney and thyroid tests, before any big diet change.';
        }

        return self::result('cat', $stage.' cat', $points, $answers, [
            $stage.' cat food complete '.($style === 'dry' ? 'dry' : 'wet'),
            'cat water fountain',
            'cat slow feeder',
        ], $country);
    }

    private static function dog(array $answers, string $age, string $country): array
    {
        $size = (string) ($answers['size'] ?? 'medium');
        $stage = match ($age) {
            'young' => 'puppy',
            'senior' => 'senior',
            default => 'adult',
        };
        $points = [
            'Choose a food labelled "complete" for '.$stage.' dogs'.($size === 'large' && $stage === 'puppy' ? ', made for large-breed puppies. They need controlled calcium to grow at the right pace.' : '.'),
            match ((string) ($answers['activity'] ?? 'normal')) {
                'high' => 'Very active or working dogs need more energy. Look for higher calorie "active" or "working" formulas.',
                'low' => 'Relaxed dogs need fewer calories. Use the lower end of the feeding guide.',
                default => 'Start with the feeding guide on the pack for your dog\'s ideal weight, then adjust.',
            },
            'Weigh food with kitchen scales. Cups and scoops are easy to overfill.',
        ];

        if ($size === 'small') {
            $points[] = 'Small dogs do well on small kibble and may need three small meals to avoid low blood sugar.';
        }

        return self::result('dog', $stage.' dog', $points, $answers, [
            $stage.' dog food complete '.$size.' breed',
            'dog slow feeder bowl',
            'kitchen scales pet food',
        ], $country);
    }

    private static function rabbit(string $age, string $country): array
    {
        $points = [
            'Hay is 80 to 85% of a healthy rabbit diet. Give a pile at least the size of your rabbit every day, and keep it topped up.',
            'Add a handful of fresh leafy greens and herbs, morning and evening.',
            'Pellets: an egg cup per kilo of rabbit a day. Choose single-type pellets, not muesli mixes, which cause dental problems.',
            'Fruit and carrots are treats only.',
        ];

        if ($age === 'young') {
            $points[0] = 'Young rabbits under about 6 months can have unlimited hay and a little more pellet food while they grow.';
        }

        return self::result('rabbit', 'rabbit', $points, [], ['timothy hay rabbit', 'rabbit nuggets pellets', 'hay rack rabbit'], $country);
    }

    private static function guineaPig(string $country): array
    {
        return self::result('guinea-pig', 'guinea pig', [
            'Unlimited good-quality hay, every day.',
            'Guinea pigs cannot make their own vitamin C. Feed a pelleted food with added vitamin C, plus fresh vitamin C-rich vegetables such as peppers and kale.',
            'Avoid muesli mixes. Guinea pigs pick out the sweet bits.',
            'Always keep them in pairs or groups.',
        ], [], ['guinea pig pellets vitamin c', 'timothy hay', 'guinea pig hay rack'], $country);
    }

    private static function result(string $species, string $title, array $points, array $answers, array $searches, string $country): array
    {
        if (($answers['weight'] ?? 'ideal') === 'over') {
            $points[] = 'Carrying extra weight: ask your vet nurse about a free weight clinic and a target weight before cutting food. Count treats.';
        }

        if (($answers['weight'] ?? 'ideal') === 'under') {
            $points[] = 'Looking thin: book a vet check first. Weight loss can be a sign of illness, especially in older pets.';
        }

        if (($answers['tummy'] ?? 'no') === 'yes') {
            $points[] = 'Sensitive tummy or itchy skin: ask your vet before switching. Any change should happen slowly over 7 to 10 days. Food trials for allergies need to be done with a vet.';
        }

        $points[] = 'Change food gradually over 7 to 10 days, mixing more of the new food in each day.';

        $tag = (string) config($country === 'US' ? 'catmarket.amazon.us' : 'catmarket.amazon.uk', '');
        $host = $country === 'US' ? 'https://www.amazon.com' : 'https://www.amazon.co.uk';

        return [
            'species' => $species,
            'title' => 'Feeding a '.$title,
            'points' => $points,
            'shopping' => collect($searches)->map(static fn (string $search): array => [
                'label' => ucfirst($search),
                'url' => $host.'/s?k='.rawurlencode($search).($tag !== '' ? '&tag='.rawurlencode($tag) : ''),
            ])->all(),
            'affiliate' => $tag !== '',
        ];
    }
}
