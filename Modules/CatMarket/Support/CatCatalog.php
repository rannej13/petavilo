<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Str;
use Modules\Listing\Models\ListingCustomField;

final class CatCatalog
{
    public const SELLER_PRIVATE = 'Private owner';

    public const SELLER_RESCUE = 'Rescue or charity';

    public const SELLER_MOTHER_OWNER = 'Owner of the mother (I bred this litter)';

    public const SELLER_LICENSED = 'Licensed seller (council licence)';

    public const SELLER_REGISTERED_BREEDER = 'Registered breeder (GCCF, TICA or FIFe)';

    public static function categories(): array
    {
        return [
            ['name' => 'Cats for Rehoming', 'slug' => 'cats-for-rehoming', 'icon' => 'img/category/pet.png', 'description' => 'Adult and senior cats for adoption and rehoming in the UK and US. Every cat lists age, vaccinations, microchip and who is rehoming.', 'children' => ['Adult cats', 'Senior cats', 'Bonded pairs']],
            ['name' => 'Kittens', 'slug' => 'kittens', 'icon' => 'img/category/pet.png', 'description' => 'Kittens for sale and adoption in the UK and US from owners of the mother, licensed sellers and registered breeders.', 'children' => ['Non-pedigree kittens', 'Pedigree kittens']],
            ['name' => 'Pedigree Cats & Breeders', 'slug' => 'pedigree-cats', 'icon' => 'img/category/pet.png', 'description' => 'Pedigree cats and kittens from registered breeders: Maine Coon, Ragdoll, British Shorthair, Bengal and more.', 'children' => ['Adult pedigree cats', 'Upcoming litters']],
            ['name' => 'Lost & Found', 'slug' => 'lost-and-found', 'icon' => 'img/category/pet.png', 'description' => 'Report a lost or found cat near you in the UK or US. Free to post.', 'children' => ['Lost cats', 'Found cats']],
            ['name' => 'Cat Products', 'slug' => 'cat-products', 'icon' => 'img/category/home_tools.png', 'description' => 'Buy and sell cat trees, carriers, toys, bowls, litter trays and other cat supplies.', 'children' => ['Cat trees & furniture', 'Carriers & travel', 'Toys', 'Bowls & feeders', 'Litter & trays', 'Other products']],
            ['name' => 'Cat Services', 'slug' => 'cat-services', 'icon' => 'img/category/home_tools.png', 'description' => 'Find cat sitters, groomers, cat boarding and behaviour help near you.', 'children' => ['Cat sitting', 'Grooming', 'Boarding', 'Behaviour & training', 'Pet transport']],
        ];
    }

    public static function childSlug(string $rootSlug, string $childName): string
    {
        return $rootSlug.'-'.Str::slug($childName);
    }

    public static function fieldsFor(string $rootSlug, string $leafSlug): array
    {
        return match ($rootSlug) {
            'cats-for-rehoming' => [
                self::select('seller_type', 'Who is rehoming this cat?', [self::SELLER_PRIVATE, self::SELLER_RESCUE], true),
                self::text('charity_number', 'Charity or nonprofit number', 'UK charity number or US EIN', 'Shown on the advert'),
                ...self::animalCore(),
            ],
            'kittens' => [
                self::select('seller_type', 'Who is selling these kittens?', [self::SELLER_MOTHER_OWNER, self::SELLER_LICENSED, self::SELLER_REGISTERED_BREEDER], true, 'Advertise only kittens you bred yourself unless you hold a licence. In England third-party sales of kittens under 6 months are illegal; in the US follow your state rules.'),
                ...self::licenceFields(),
                ...self::animalCore(),
                self::date('ready_to_leave', 'Ready to leave', true, 'Kittens must be at least 8 weeks old before they leave their mother.'),
                self::boolean('mother_seen', 'Buyers can see the kittens with their mother', true),
                ...($leafSlug === self::childSlug('kittens', 'Pedigree kittens') ? [self::registration()] : []),
            ],
            'pedigree-cats' => [
                self::select('seller_type', 'Who is selling?', [self::SELLER_REGISTERED_BREEDER, self::SELLER_LICENSED, self::SELLER_PRIVATE], true),
                ...self::licenceFields(),
                ...self::animalCore(),
                self::registration(),
                self::date('ready_to_leave', 'Ready to leave (litters)', false, 'For upcoming litters only.'),
            ],
            'lost-and-found' => [
                self::date('date_lost_found', 'Date lost or found', true),
                self::text('area_last_seen', 'Area last seen', 'Street or landmark, town', 'Do not post your full address.', true),
                self::text('colour_markings', 'Colour and markings', 'Black with white chest and paws', null, true),
                self::select('microchip_status', 'Microchipped?', ['Yes', 'No', 'Unknown'], true),
                self::boolean('wearing_collar', 'Wearing a collar'),
            ],
            'cat-products' => [
                self::select('condition', 'Condition', ['New', 'Like new', 'Used - good', 'Used - fair'], true),
                self::text('brand', 'Brand', 'Brand name'),
            ],
            'cat-services' => [
                self::text('area_covered', 'Area covered', 'Leeds and 10 miles around', null, true),
                self::boolean('insured', 'I have public liability insurance'),
                self::text('qualifications', 'Qualifications or licence', 'e.g. council boarding licence number'),
            ],
            default => [],
        };
    }

    public static function breeds(): array
    {
        return [
            'Domestic shorthair (moggy)', 'Domestic longhair (moggy)', 'Crossbreed',
            'Abyssinian', 'Bengal', 'Birman', 'Bombay', 'British Longhair', 'British Shorthair',
            'Burmese', 'Burmilla', 'Chartreux', 'Cornish Rex', 'Devon Rex', 'Egyptian Mau',
            'Exotic Shorthair', 'Havana', 'Korat', 'LaPerm', 'Maine Coon', 'Manx',
            'Norwegian Forest Cat', 'Ocicat', 'Oriental', 'Persian', 'Ragamuffin', 'Ragdoll',
            'Russian Blue', 'Selkirk Rex', 'Siamese', 'Siberian', 'Somali', 'Sphynx',
            'Tonkinese', 'Turkish Angora', 'Turkish Van', 'Other',
        ];
    }

    private static function animalCore(): array
    {
        return [
            self::select('breed', 'Breed', self::breeds(), true),
            self::date('date_of_birth', 'Date of birth', true, 'An estimate is fine for rescue cats.'),
            self::select('sex', 'Sex', ['Male', 'Female', 'Mixed litter'], true),
            self::select('neutered', 'Neutered or spayed', ['Yes', 'No', 'Booked', 'Too young'], true),
            self::select('vaccinations', 'Vaccinations', ['None', 'First vaccination', 'Full course', 'Boosters up to date'], true),
            self::boolean('microchipped', 'Microchipped and registered on a database', true, 'Required by law in England by 20 weeks old. Strongly recommended everywhere.'),
            self::boolean('flea_worm_treated', 'Flea and worm treated', true),
            self::date('vet_checked', 'Last vet health check'),
            self::select('lifestyle', 'Lifestyle', ['Indoor only', 'Outdoor access', 'Either'], true),
            self::select('good_with', 'Good with', ['Unknown', 'Adults only', 'Children', 'Other cats', 'Dogs', 'Children and other pets'], false),
        ];
    }

    private static function licenceFields(): array
    {
        return [
            self::text('licence_number', 'Licence number', 'Council (UK), state or USDA (US) licence', 'UK licensed sellers must show their licence number by law.'),
            self::text('licence_council', 'Issued by', 'Leeds City Council, or state agency'),
        ];
    }

    private static function registration(): array
    {
        return self::select('registration', 'Pedigree registration', ['GCCF', 'TICA', 'FIFe', 'Other', 'Not registered'], true);
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
