<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Str;
use Modules\Listing\Models\ListingCustomField;

final class PetCatalog
{
    public const SELLER_PRIVATE = 'Private owner';

    public const SELLER_RESCUE = 'Rescue or charity';

    public const SELLER_MOTHER_OWNER = 'Owner of the mother (I bred this litter)';

    public const SELLER_LICENSED = 'Licensed seller or breeder (council licence)';

    public const SELLER_REGISTERED_BREEDER = 'Registered breeder';

    public const KIND_YOUNG = 'young';

    public const KIND_PEDIGREE = 'pedigree';

    public const KIND_LITTER = 'litter';

    public const KIND_REHOMING = 'rehoming';

    public const KIND_SALE = 'sale';

    public const KIND_LOST_FOUND = 'lost-found';

    public const KIND_PRODUCTS = 'products';

    public const KIND_SERVICES = 'services';

    public const ANIMAL_KINDS = [self::KIND_YOUNG, self::KIND_PEDIGREE, self::KIND_LITTER, self::KIND_REHOMING, self::KIND_SALE];

    public static function categories(): array
    {
        return [
            self::root('Cats', 'cats', 'cat', 'Kittens and cats for sale, pedigree breeders and cats for rehoming in the UK and US.', [
                ['Kittens for sale', self::KIND_YOUNG],
                ['Pedigree cats & kittens', self::KIND_PEDIGREE],
                ['Cats for rehoming', self::KIND_REHOMING],
                ['Upcoming litters', self::KIND_LITTER],
            ]),
            self::root('Dogs', 'dogs', 'dog', 'Puppies and dogs for sale, pedigree breeders and dogs for rehoming in the UK and US.', [
                ['Puppies for sale', self::KIND_YOUNG],
                ['Pedigree dogs & puppies', self::KIND_PEDIGREE],
                ['Dogs for rehoming', self::KIND_REHOMING],
                ['Upcoming litters', self::KIND_LITTER],
            ]),
            self::root('Rabbits', 'rabbits', 'rabbit', 'Rabbits for sale and rabbits looking for new homes.', [
                ['Rabbits for sale', self::KIND_SALE],
                ['Rabbits for rehoming', self::KIND_REHOMING],
            ]),
            self::root('Small Animals', 'small-animals', 'small', 'Guinea pigs, hamsters, rats, mice, gerbils, ferrets, chinchillas and degus.', [
                ['Small animals for sale', self::KIND_SALE],
                ['Small animals for rehoming', self::KIND_REHOMING],
            ]),
            self::root('Birds', 'birds', 'bird', 'Budgies, cockatiels, canaries, parrots, finches and poultry kept as pets.', [
                ['Birds for sale', self::KIND_SALE],
                ['Birds for rehoming', self::KIND_REHOMING],
            ]),
            self::root('Reptiles', 'reptiles', 'reptile', 'Bearded dragons, leopard geckos, corn snakes, royal pythons, tortoises and other reptiles and amphibians.', [
                ['Reptiles for sale', self::KIND_SALE],
                ['Reptiles for rehoming', self::KIND_REHOMING],
            ]),
            self::root('Fish', 'fish', 'fish', 'Tropical, coldwater and pond fish.', [
                ['Fish for sale', self::KIND_SALE],
                ['Fish for rehoming', self::KIND_REHOMING],
            ]),
            self::root('Other Pets', 'other-pets', 'other', 'Any other pet that does not fit above. Wild animals and dangerous wild animals cannot be listed.', [
                ['Other pets for sale', self::KIND_SALE],
                ['Other pets for rehoming', self::KIND_REHOMING],
            ]),
            self::root('Lost & Found', 'lost-and-found', null, 'Report a lost or found pet near you in the UK or US. Free to post.', [
                ['Lost pets', self::KIND_LOST_FOUND],
                ['Found pets', self::KIND_LOST_FOUND],
            ], 'img/category/pet.png'),
            self::root('Pet Products', 'pet-products', null, 'Buy and sell cages, hutches, tanks, beds, carriers, toys and other pet supplies.', [
                ['Cat supplies', self::KIND_PRODUCTS],
                ['Dog supplies', self::KIND_PRODUCTS],
                ['Rabbit & small animal supplies', self::KIND_PRODUCTS],
                ['Bird supplies', self::KIND_PRODUCTS],
                ['Fish & reptile supplies', self::KIND_PRODUCTS],
                ['Other pet supplies', self::KIND_PRODUCTS],
            ], 'img/category/home_tools.png'),
            self::root('Pet Services', 'pet-services', null, 'Find pet sitters, dog walkers, groomers, boarding, kennels, catteries and trainers near you.', [
                ['Pet sitting', self::KIND_SERVICES],
                ['Dog walking', self::KIND_SERVICES],
                ['Grooming', self::KIND_SERVICES],
                ['Boarding, kennels & catteries', self::KIND_SERVICES],
                ['Training & behaviour', self::KIND_SERVICES],
                ['Pet transport', self::KIND_SERVICES],
            ], 'img/category/home_tools.png'),
        ];
    }

    public static function legacySlugMap(): array
    {
        return [
            'cats-for-rehoming' => 'cats-cats-for-rehoming',
            'cats-for-rehoming-adult-cats' => 'cats-cats-for-rehoming',
            'cats-for-rehoming-senior-cats' => 'cats-cats-for-rehoming',
            'cats-for-rehoming-bonded-pairs' => 'cats-cats-for-rehoming',
            'kittens' => 'cats-kittens-for-sale',
            'kittens-non-pedigree-kittens' => 'cats-kittens-for-sale',
            'kittens-pedigree-kittens' => 'cats-pedigree-cats-kittens',
            'pedigree-cats' => 'cats-pedigree-cats-kittens',
            'pedigree-cats-adult-pedigree-cats' => 'cats-pedigree-cats-kittens',
            'pedigree-cats-upcoming-litters' => 'cats-upcoming-litters',
            'lost-and-found-lost-cats' => 'lost-and-found-lost-pets',
            'lost-and-found-found-cats' => 'lost-and-found-found-pets',
            'cat-products' => 'pet-products-cat-supplies',
            'cat-products-cat-trees-furniture' => 'pet-products-cat-supplies',
            'cat-products-carriers-travel' => 'pet-products-cat-supplies',
            'cat-products-toys' => 'pet-products-cat-supplies',
            'cat-products-bowls-feeders' => 'pet-products-cat-supplies',
            'cat-products-litter-trays' => 'pet-products-cat-supplies',
            'cat-products-other-products' => 'pet-products-cat-supplies',
            'cat-services' => 'pet-services-pet-sitting',
            'cat-services-cat-sitting' => 'pet-services-pet-sitting',
            'cat-services-grooming' => 'pet-services-grooming',
            'cat-services-boarding' => 'pet-services-boarding-kennels-catteries',
            'cat-services-behaviour-training' => 'pet-services-training-behaviour',
            'cat-services-pet-transport' => 'pet-services-pet-transport',
        ];
    }

    public static function childSlug(string $rootSlug, string $childName): string
    {
        return $rootSlug.'-'.Str::slug($childName);
    }

    public static function leaves(): array
    {
        $leaves = [];

        foreach (self::categories() as $root) {
            foreach ($root['children'] as [$name, $kind]) {
                $leaves[self::childSlug($root['slug'], $name)] = [
                    'name' => $name,
                    'kind' => $kind,
                    'root' => $root['slug'],
                    'species' => $root['species'],
                ];
            }
        }

        return $leaves;
    }

    public static function leaf(string $slug): ?array
    {
        return self::leaves()[$slug] ?? null;
    }

    public static function animalRootSlugs(): array
    {
        return collect(self::categories())->whereNotNull('species')->pluck('slug')->values()->all();
    }

    public static function speciesForRoot(?string $rootSlug): ?string
    {
        return collect(self::categories())->firstWhere('slug', $rootSlug)['species'] ?? null;
    }

    public static function fieldsFor(string $leafSlug): array
    {
        $leaf = self::leaf($leafSlug);

        if ($leaf === null) {
            return [];
        }

        $species = (string) $leaf['species'];

        return match ($leaf['kind']) {
            self::KIND_YOUNG => [
                self::select('seller_type', 'Who is selling?', [self::SELLER_MOTHER_OWNER, self::SELLER_LICENSED, self::SELLER_REGISTERED_BREEDER], true, 'Advertise only '.self::youngWord($species).' you bred yourself unless you hold a licence. In the UK, third-party sales of puppies and kittens under 6 months are illegal; in the US follow your state rules.'),
                ...self::licenceFields(),
                ...self::animalCore($species),
                self::date('ready_to_leave', 'Ready to leave', true, ucfirst(self::youngWord($species)).' must be at least 8 weeks old before they leave their mother.'),
                self::boolean('mother_seen', 'Buyers can see them with their mother', true),
            ],
            self::KIND_PEDIGREE, self::KIND_LITTER => [
                self::select('seller_type', 'Who is selling?', [self::SELLER_REGISTERED_BREEDER, self::SELLER_LICENSED, self::SELLER_MOTHER_OWNER, self::SELLER_PRIVATE], true),
                ...self::licenceFields(),
                ...self::animalCore($species),
                self::registration($species),
                self::date('ready_to_leave', 'Ready to leave', $leaf['kind'] === self::KIND_LITTER, 'For litters. Must be at least 8 weeks after birth.'),
                self::boolean('mother_seen', 'Buyers can see them with their mother'),
            ],
            self::KIND_REHOMING => [
                self::select('seller_type', 'Who is rehoming?', [self::SELLER_PRIVATE, self::SELLER_RESCUE], true, 'Rescues and charities get a verified badge once we have checked their charity details.'),
                self::text('charity_number', 'Charity or nonprofit number', 'UK charity number or US EIN', 'Rescues only. Shown on the advert.'),
                ...self::speciesPicker($species),
                ...self::animalCore($species),
                self::text('rehoming_reason', 'Why are you rehoming?', 'e.g. moving to a home that does not allow pets', 'Honest reasons help find the right home.', true),
            ],
            self::KIND_SALE => [
                self::select('seller_type', 'Who is selling?', [self::SELLER_PRIVATE, self::SELLER_MOTHER_OWNER, self::SELLER_LICENSED, self::SELLER_REGISTERED_BREEDER], true, 'Pet shops and anyone selling pets as a business need a council licence in the UK.'),
                ...self::licenceFields(),
                ...self::speciesPicker($species),
                ...self::animalCore($species),
            ],
            self::KIND_LOST_FOUND => [
                self::select('species', 'Type of pet', ['Cat', 'Dog', 'Rabbit', 'Small animal', 'Bird', 'Reptile', 'Other'], true),
                self::date('date_lost_found', 'Date lost or found', true),
                self::text('area_last_seen', 'Area last seen', 'Street or landmark, town', 'Do not post your full address.', true),
                self::text('colour_markings', 'Colour and markings', 'Black with white chest and paws', null, true),
                self::select('microchip_status', 'Microchipped?', ['Yes', 'No', 'Unknown'], true, 'Never post the microchip number. A vet or warden can scan it.'),
                self::boolean('wearing_collar', 'Wearing a collar'),
            ],
            self::KIND_PRODUCTS => [
                self::select('condition', 'Condition', ['New', 'Like new', 'Used - good', 'Used - fair'], true),
                self::text('brand', 'Brand', 'Brand name'),
            ],
            self::KIND_SERVICES => [
                self::text('area_covered', 'Area covered', 'Leeds and 10 miles around', null, true),
                self::boolean('insured', 'I have public liability insurance'),
                self::text('qualifications', 'Qualifications or licence', 'e.g. council boarding licence number'),
            ],
            default => [],
        };
    }

    public static function breeds(string $species = 'cat'): array
    {
        return match ($species) {
            'cat' => [
                'Domestic shorthair (moggy)', 'Domestic longhair (moggy)', 'Crossbreed',
                'Abyssinian', 'Bengal', 'Birman', 'Bombay', 'British Longhair', 'British Shorthair',
                'Burmese', 'Burmilla', 'Chartreux', 'Cornish Rex', 'Devon Rex', 'Egyptian Mau',
                'Exotic Shorthair', 'Havana', 'Korat', 'LaPerm', 'Maine Coon', 'Manx',
                'Norwegian Forest Cat', 'Ocicat', 'Oriental', 'Persian', 'Ragamuffin', 'Ragdoll',
                'Russian Blue', 'Selkirk Rex', 'Siamese', 'Siberian', 'Somali', 'Sphynx',
                'Tonkinese', 'Turkish Angora', 'Turkish Van', 'Other',
            ],
            'dog' => [
                'Crossbreed', 'Akita', 'Alaskan Malamute', 'Australian Shepherd', 'Basset Hound', 'Beagle',
                'Bernese Mountain Dog', 'Bichon Frise', 'Border Collie', 'Border Terrier', 'Boston Terrier',
                'Boxer', 'Bulldog', 'Cavalier King Charles Spaniel', 'Cavapoo', 'Chihuahua', 'Chow Chow',
                'Cocker Spaniel', 'Cockapoo', 'Dachshund', 'Dalmatian', 'Dobermann', 'English Springer Spaniel',
                'French Bulldog', 'German Shepherd', 'German Shorthaired Pointer', 'Golden Retriever',
                'Goldendoodle', 'Great Dane', 'Greyhound', 'Havanese', 'Husky', 'Jack Russell Terrier',
                'Labradoodle', 'Labrador Retriever', 'Lhasa Apso', 'Lurcher', 'Maltese', 'Miniature Schnauzer',
                'Newfoundland', 'Pomeranian', 'Poodle', 'Pug', 'Rhodesian Ridgeback', 'Rottweiler',
                'Shar Pei', 'Shetland Sheepdog', 'Shiba Inu', 'Shih Tzu', 'Staffordshire Bull Terrier',
                'Vizsla', 'Weimaraner', 'West Highland Terrier', 'Whippet', 'Yorkshire Terrier', 'Other',
            ],
            'rabbit' => [
                'Crossbreed', 'Netherland Dwarf', 'Mini Lop', 'Holland Lop', 'Dwarf Lop', 'French Lop',
                'Lionhead', 'Rex', 'Mini Rex', 'Dutch', 'English Lop', 'Flemish Giant', 'Continental Giant',
                'Angora', 'Harlequin', 'Himalayan', 'Other',
            ],
            default => [],
        };
    }

    public static function speciesOptions(string $species): array
    {
        return match ($species) {
            'small' => ['Guinea pig', 'Hamster', 'Rat', 'Mouse', 'Gerbil', 'Ferret', 'Chinchilla', 'Degu', 'Hedgehog (African pygmy)', 'Other'],
            'bird' => ['Budgie', 'Cockatiel', 'Canary', 'Finch', 'Lovebird', 'Parrot', 'African grey', 'Conure', 'Chicken', 'Duck', 'Pigeon or dove', 'Other'],
            'reptile' => ['Bearded dragon', 'Leopard gecko', 'Crested gecko', 'Corn snake', 'Royal python', 'King snake', 'Tortoise', 'Turtle or terrapin', 'Chameleon', 'Axolotl', 'Frog or toad', 'Other'],
            'fish' => ['Tropical', 'Coldwater', 'Goldfish', 'Koi or pond fish', 'Marine', 'Shrimp or invertebrates', 'Other'],
            'other' => ['Horse or pony', 'Goat', 'Sheep', 'Pig', 'Alpaca', 'Invertebrate', 'Other'],
            default => [],
        };
    }

    private static function root(string $name, string $slug, ?string $species, string $description, array $children, string $icon = 'img/category/pet.png'): array
    {
        return compact('name', 'slug', 'species', 'description', 'children', 'icon');
    }

    private static function youngWord(string $species): string
    {
        return $species === 'dog' ? 'puppies' : 'kittens';
    }

    private static function speciesPicker(string $species): array
    {
        $options = self::speciesOptions($species);

        return $options === [] ? [] : [self::select('species', 'What kind?', $options, true)];
    }

    private static function animalCore(string $species): array
    {
        $breeds = self::breeds($species);
        $mammal = in_array($species, ['cat', 'dog', 'rabbit', 'small'], true);
        $chipHelp = match ($species) {
            'dog' => 'Required by law in the UK from 8 weeks old.',
            'cat' => 'Required by law in England by 20 weeks old.',
            default => 'Recommended where possible.',
        };

        return array_values(array_filter([
            $breeds !== [] ? self::select('breed', 'Breed', $breeds, true) : self::text('breed', 'Breed or variety', 'If known'),
            self::date('date_of_birth', 'Date of birth', in_array($species, ['cat', 'dog'], true), 'An estimate is fine. Buyers see the age worked out from this date.'),
            self::select('sex', 'Sex', ['Male', 'Female', 'Mixed litter or group', 'Unknown'], true),
            $mammal ? self::select('neutered', 'Neutered or spayed', ['Yes', 'No', 'Booked', 'Too young', 'Unknown'], true) : null,
            $mammal ? self::select('vaccinations', 'Vaccinations', ['Unknown', 'None', 'First vaccination', 'Full course', 'Boosters up to date'], true) : null,
            self::select('health_evidence', 'Health and vaccination records', ['Unknown', 'Owner says (no paperwork)', 'Vet records available to see'], true, 'Unknown is fine. Never guess.'),
            in_array($species, ['cat', 'dog', 'rabbit', 'small'], true) || $species === 'reptile'
                ? self::select('microchip', 'Microchipped and registered', ['Yes', 'No', 'Unknown'], in_array($species, ['cat', 'dog'], true), $chipHelp)
                : null,
            $mammal ? self::boolean('flea_worm_treated', 'Flea and worm treated') : null,
            self::date('vet_checked', 'Last vet health check'),
            self::select('good_with', 'Good with', ['Unknown', 'Adults only', 'Children', 'Other pets', 'Children and other pets'], false),
        ]));
    }

    private static function licenceFields(): array
    {
        return [
            self::text('licence_number', 'Licence number', 'Council (UK), state or USDA (US) licence', 'UK licensed sellers and breeders must show their licence number by law.'),
            self::text('licence_council', 'Issued by', 'Leeds City Council, or state agency'),
        ];
    }

    private static function registration(string $species): array
    {
        $options = match ($species) {
            'dog' => ['The Kennel Club (UK)', 'AKC (US)', 'UKC (US)', 'Other', 'Not registered'],
            default => ['GCCF', 'TICA', 'FIFe', 'CFA', 'Other', 'Not registered'],
        };

        return self::select('registration', 'Pedigree registration', $options, true);
    }

    private static function text(string $name, string $label, ?string $placeholder = null, ?string $help = null, bool $required = false): array
    {
        return ['name' => $name, 'label' => $label, 'type' => ListingCustomField::TYPE_TEXT, 'placeholder' => $placeholder, 'help_text' => $help, 'is_required' => $required];
    }

    private static function select(string $name, string $label, array $options, bool $required = false, ?string $help = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => ListingCustomField::TYPE_SELECT, 'options' => $options, 'is_required' => $required, 'help_text' => $help];
    }

    private static function boolean(string $name, string $label, bool $required = false, ?string $help = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => ListingCustomField::TYPE_BOOLEAN, 'is_required' => $required, 'help_text' => $help];
    }

    private static function date(string $name, string $label, bool $required = false, ?string $help = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => ListingCustomField::TYPE_DATE, 'is_required' => $required, 'help_text' => $help];
    }
}
