<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

use Illuminate\Support\Str;

final class ToxinLibrary
{
    public const DANGER = 'danger';

    public const TOXIC = 'toxic';

    public const CAUTION = 'caution';

    public const SAFE = 'safe';

    public static function species(): array
    {
        return ['dog' => 'Dogs', 'cat' => 'Cats', 'rabbit' => 'Rabbits and small pets', 'bird' => 'Birds'];
    }

    public static function levels(): array
    {
        return [
            self::DANGER => ['label' => 'Emergency', 'advice' => 'Call your vet now, even if your pet seems fine.', 'tone' => 'badge--critical'],
            self::TOXIC => ['label' => 'Poisonous', 'advice' => 'Call your vet or a poison line for advice today.', 'tone' => 'badge--caution'],
            self::CAUTION => ['label' => 'Can upset', 'advice' => 'Usually mild. Watch closely and call your vet if you are worried.', 'tone' => ''],
            self::SAFE => ['label' => 'Generally safe', 'advice' => 'Safe in small amounts for most pets.', 'tone' => 'badge--positive'],
        ];
    }

    public static function categories(): array
    {
        return ['food' => 'Food and drink', 'plant' => 'Plants and flowers', 'medicine' => 'Medicines', 'household' => 'Home and garden products'];
    }

    public static function search(string $query, ?string $species, ?string $category): array
    {
        $needle = Str::lower(trim($query));

        return collect(self::all())
            ->filter(static function (array $item) use ($needle, $species, $category): bool {
                if ($category !== null && $item['category'] !== $category) {
                    return false;
                }

                if ($species !== null && ! isset($item['risk'][$species])) {
                    return false;
                }

                if ($needle === '') {
                    return true;
                }

                $haystack = Str::lower($item['name'].' '.implode(' ', $item['aliases']));

                return str_contains($haystack, $needle);
            })
            ->sortBy(static fn (array $item): string => $item['name'])
            ->values()
            ->all();
    }

    public static function all(): array
    {
        return [
            self::item('Chocolate', ['cocoa', 'brownie', 'cake', 'easter egg', 'hot chocolate'], 'food', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::TOXIC, 'bird' => self::DANGER], 'Contains theobromine and caffeine. Dark and baking chocolate are the most dangerous, but any amount is worth a call.'),
            self::item('Xylitol (birch sugar)', ['sugar free gum', 'sweetener', 'sugar-free sweets', 'some peanut butters', 'chewing gum', 'e967'], 'food', ['dog' => self::DANGER, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC, 'bird' => self::TOXIC], 'Causes a sudden drop in blood sugar and liver damage in dogs. Check peanut butter and sugar-free products for xylitol or birch sugar.'),
            self::item('Grapes, raisins, sultanas and currants', ['grape', 'raisin', 'sultana', 'currant', 'fruit cake', 'hot cross bun', 'mince pie', 'christmas pudding'], 'food', ['dog' => self::DANGER, 'cat' => self::TOXIC, 'rabbit' => self::CAUTION, 'bird' => self::CAUTION], 'Can cause kidney failure in dogs, and there is no known safe amount. Cooked and baked forms count too.'),
            self::item('Onions, garlic, leeks and chives', ['onion', 'garlic', 'leek', 'chive', 'shallot', 'gravy', 'onion powder', 'garlic powder'], 'food', ['dog' => self::TOXIC, 'cat' => self::DANGER, 'rabbit' => self::TOXIC, 'bird' => self::TOXIC], 'Damage red blood cells. Cooked, raw and powdered forms are all a risk. Cats are especially sensitive.'),
            self::item('Macadamia nuts', ['macadamia'], 'food', ['dog' => self::TOXIC, 'cat' => self::CAUTION], 'Can cause weakness, wobbliness and a high temperature in dogs.'),
            self::item('Alcohol', ['beer', 'wine', 'spirits', 'liqueur'], 'food', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Pets are far more sensitive than people. Small amounts can be dangerous.'),
            self::item('Caffeine', ['coffee', 'tea bags', 'energy drink', 'caffeine tablets', 'coffee grounds'], 'food', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC, 'bird' => self::DANGER], 'Causes a racing heart and agitation. Caffeine tablets and coffee grounds are the most concentrated.'),
            self::item('Avocado', ['avocado', 'guacamole'], 'food', ['dog' => self::CAUTION, 'cat' => self::CAUTION, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Contains persin, which can be fatal to birds and rabbits. For dogs, the stone is a choking and blockage risk.'),
            self::item('Raw bread dough', ['dough', 'yeast', 'pizza dough'], 'food', ['dog' => self::DANGER, 'cat' => self::TOXIC], 'Rises in the stomach and produces alcohol as it ferments.'),
            self::item('Hops', ['brewing', 'home brew'], 'food', ['dog' => self::DANGER], 'Can cause a dangerously high temperature in dogs.'),
            self::item('Cooked bones', ['chicken bones', 'lamb bones', 'turkey bones'], 'food', ['dog' => self::TOXIC, 'cat' => self::TOXIC], 'Not poisonous, but they splinter and can cause choking, tears and blockages.'),
            self::item('Salt, play dough and salt dough', ['salt', 'playdough', 'play-doh', 'salt dough ornament', 'rock salt', 'sea water'], 'food', ['dog' => self::DANGER, 'cat' => self::TOXIC], 'Large amounts of salt cause dangerous changes in the blood. Homemade salt dough decorations are a common cause.'),
            self::item('Mouldy food and compost', ['mould', 'mold', 'compost', 'bin'], 'food', ['dog' => self::DANGER, 'cat' => self::TOXIC], 'Mould toxins can cause tremors and seizures.'),
            self::item('Milk and dairy', ['milk', 'cream', 'cheese'], 'food', ['dog' => self::CAUTION, 'cat' => self::CAUTION, 'rabbit' => self::TOXIC], 'Many adult cats and dogs cannot digest lactose and get diarrhoea. Never give to rabbits.'),
            self::item('Fat trimmings and fatty food', ['fat', 'bacon', 'sausages', 'roast dinner', 'takeaway'], 'food', ['dog' => self::CAUTION, 'cat' => self::CAUTION], 'Can trigger pancreatitis in dogs, which can be serious.'),
            self::item('Fruit stones and apple seeds', ['cherry stone', 'plum stone', 'peach stone', 'apricot', 'apple core'], 'food', ['dog' => self::CAUTION, 'cat' => self::CAUTION, 'rabbit' => self::CAUTION, 'bird' => self::TOXIC], 'Stones are a choking and blockage risk, and the kernels contain compounds that release cyanide when chewed.'),
            self::item('Iceberg lettuce', ['lettuce'], 'food', ['rabbit' => self::CAUTION], 'Not poisonous, but watery and low in nutrients and can cause loose droppings. Darker leaves such as romaine are better.'),
            self::item('Carrots', ['carrot'], 'food', ['dog' => self::SAFE, 'rabbit' => self::CAUTION], 'Safe for dogs. Sugary for rabbits, so a small treat only.'),
            self::item('Plain cooked chicken', ['chicken'], 'food', ['dog' => self::SAFE, 'cat' => self::SAFE], 'Plain, boneless and unseasoned is safe as a treat.'),
            self::item('Lilies', ['lily', 'day lily', 'easter lily', 'tiger lily', 'stargazer', 'bouquet', 'flowers'], 'plant', ['cat' => self::DANGER, 'dog' => self::CAUTION], 'Every part is dangerous to cats, including pollen and vase water, and can cause kidney failure. Peace lilies and calla lilies are less severe but still irritant.'),
            self::item('Sago palm', ['cycad', 'cardboard palm'], 'plant', ['dog' => self::DANGER, 'cat' => self::DANGER], 'Can cause liver failure. The seeds are the most toxic part.'),
            self::item('Daffodils', ['daffodil', 'narcissus', 'bulbs'], 'plant', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC], 'Bulbs are the most toxic part. Can cause vomiting and, in larger amounts, heart problems.'),
            self::item('Tulip and hyacinth bulbs', ['tulip', 'hyacinth', 'bulbs'], 'plant', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC], 'Bulbs cause the most problems, usually vomiting and drooling.'),
            self::item('Foxglove', ['digitalis'], 'plant', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Affects the heart.'),
            self::item('Oleander', ['nerium'], 'plant', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Affects the heart. All parts are toxic.'),
            self::item('Yew', ['taxus', 'hedge'], 'plant', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'All parts except the red flesh of the berry are highly toxic, including clippings.'),
            self::item('Azalea and rhododendron', ['azalea', 'rhododendron'], 'plant', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC, 'bird' => self::TOXIC], 'Can cause vomiting, weakness and heart problems.'),
            self::item('Conkers', ['horse chestnut'], 'plant', ['dog' => self::TOXIC], 'Cause vomiting and can block the gut.'),
            self::item('Acorns', ['oak'], 'plant', ['dog' => self::TOXIC, 'rabbit' => self::TOXIC], 'Can cause stomach upset and blockages, and kidney problems in larger amounts.'),
            self::item('Ivy', ['english ivy', 'hedera'], 'plant', ['dog' => self::CAUTION, 'cat' => self::CAUTION, 'rabbit' => self::TOXIC], 'Usually causes drooling and stomach upset.'),
            self::item('Poinsettia', ['christmas plant'], 'plant', ['dog' => self::CAUTION, 'cat' => self::CAUTION], 'Mild irritation of the mouth and stomach. Less dangerous than its reputation.'),
            self::item('Mistletoe and holly', ['mistletoe', 'holly', 'berries'], 'plant', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC, 'bird' => self::TOXIC], 'Berries cause stomach upset. Mistletoe can affect the heart in larger amounts.'),
            self::item('Cyclamen', ['cyclamen'], 'plant', ['dog' => self::TOXIC, 'cat' => self::TOXIC], 'The root is the most toxic part.'),
            self::item('Ragwort', ['ragwort', 'hay'], 'plant', ['rabbit' => self::DANGER, 'dog' => self::CAUTION], 'Causes liver damage in rabbits, guinea pigs and horses, including when dried in hay.'),
            self::item('Potato and tomato plants', ['potato', 'green potato', 'tomato plant'], 'plant', ['rabbit' => self::TOXIC, 'dog' => self::CAUTION, 'cat' => self::CAUTION], 'Leaves, stems and green or raw potatoes contain solanine.'),
            self::item('Rhubarb leaves', ['rhubarb'], 'plant', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC], 'The leaves contain oxalates.'),
            self::item('Spider plant', ['chlorophytum'], 'plant', ['cat' => self::SAFE, 'dog' => self::SAFE, 'rabbit' => self::SAFE], 'Not toxic. Some cats love chewing it and may be sick afterwards.'),
            self::item('Blue-green algae', ['pond', 'lake', 'algae', 'scum'], 'household', ['dog' => self::DANGER], 'Toxic algae blooms in still water can kill dogs within hours. Keep dogs out of water with a green scum or warning signs.'),
            self::item('Paracetamol (acetaminophen)', ['paracetamol', 'acetaminophen', 'tylenol', 'calpol', 'cold and flu'], 'medicine', ['cat' => self::DANGER, 'dog' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Even one tablet can kill a cat. Never give human painkillers to pets.'),
            self::item('Ibuprofen, naproxen and aspirin', ['ibuprofen', 'nurofen', 'advil', 'naproxen', 'aspirin', 'anti-inflammatory', 'nsaid'], 'medicine', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Cause stomach ulcers and kidney damage. One of the most common poisonings reported to UK poison lines.'),
            self::item('Antidepressants, ADHD and sleep medicines', ['antidepressant', 'sertraline', 'fluoxetine', 'adhd', 'adderall', 'sleeping tablets', 'zolpidem'], 'medicine', ['dog' => self::DANGER, 'cat' => self::DANGER], 'Can cause agitation, tremors and seizures.'),
            self::item('Vitamin D and psoriasis creams', ['vitamin d', 'calcipotriol', 'dovonex', 'supplements'], 'medicine', ['dog' => self::DANGER, 'cat' => self::DANGER], 'Can cause dangerously high calcium and kidney failure.'),
            self::item('Permethrin dog flea treatment', ['flea treatment', 'spot on', 'permethrin', 'dog flea'], 'medicine', ['cat' => self::DANGER, 'dog' => self::SAFE], 'Some dog flea products contain permethrin, which causes tremors and seizures in cats. Only use products made for cats, and keep cats away from freshly treated dogs.'),
            self::item('Nicotine', ['cigarettes', 'vape', 'e-liquid', 'nicotine gum', 'nicotine pouches', 'patches'], 'household', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'E-liquids and gum are the most concentrated sources.'),
            self::item('Cannabis and THC edibles', ['cannabis', 'weed', 'thc', 'edibles', 'gummies'], 'household', ['dog' => self::TOXIC, 'cat' => self::TOXIC], 'Causes wobbliness and dribbling. Edibles may also contain chocolate or xylitol.'),
            self::item('Antifreeze and screen wash', ['antifreeze', 'ethylene glycol', 'screenwash', 'de-icer', 'coolant'], 'household', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Tastes sweet and causes kidney failure. Treatment works best within a few hours, so do not wait for signs.'),
            self::item('Slug pellets', ['slug pellets', 'metaldehyde', 'snail bait'], 'household', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Older metaldehyde pellets cause seizures. Bring the packet to the vet.'),
            self::item('Rat and mouse poison', ['rodenticide', 'rat bait', 'mouse bait', 'poison blocks'], 'household', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Many cause internal bleeding days later. Pets can also be poisoned by eating a poisoned rodent.'),
            self::item('Button and coin batteries', ['battery', 'batteries', 'button cell'], 'household', ['dog' => self::DANGER, 'cat' => self::DANGER, 'rabbit' => self::DANGER, 'bird' => self::DANGER], 'Can burn through the throat or gut within hours.'),
            self::item('Essential oils and diffusers', ['tea tree', 'eucalyptus', 'peppermint oil', 'diffuser', 'wax melts', 'essential oil'], 'household', ['cat' => self::TOXIC, 'dog' => self::TOXIC, 'bird' => self::TOXIC, 'rabbit' => self::TOXIC], 'Concentrated oils on the skin or fur are the biggest risk, especially tea tree oil for cats.'),
            self::item('Non-stick pan fumes, aerosols and candles', ['teflon', 'ptfe', 'non-stick', 'air freshener', 'candle', 'self-cleaning oven'], 'household', ['bird' => self::DANGER], 'Fumes from overheated non-stick pans can kill pet birds within minutes. Keep birds away from the kitchen.'),
            self::item('Bleach and cleaning products', ['bleach', 'cleaner', 'detergent', 'dishwasher tablet', 'laundry pod'], 'household', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC, 'bird' => self::TOXIC], 'Can burn the mouth and stomach. Do not make your pet sick.'),
            self::item('Weedkiller and fertiliser', ['weedkiller', 'fertiliser', 'fertilizer', 'plant food', 'blood bone and fish'], 'household', ['dog' => self::TOXIC, 'cat' => self::TOXIC, 'rabbit' => self::TOXIC], 'Keep pets off treated areas until dry, as the label says. Bone meal fertilisers are tempting to dogs.'),
        ];
    }

    private static function item(string $name, array $aliases, string $category, array $risk, string $note): array
    {
        return ['slug' => Str::slug($name), 'name' => $name, 'aliases' => $aliases, 'category' => $category, 'risk' => $risk, 'note' => $note];
    }
}
