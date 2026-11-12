<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\CatMarket\Support\CatCatalog;
use Modules\CatMarket\Support\SiteImages;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;
use Throwable;

class ExampleListingSeeder extends Seeder
{
    public const EMAIL = 'examples@petavilo.invalid';

    public function run(): void
    {
        if (! (bool) config('catmarket.show_examples', true)) {
            Listing::query()->where('is_example', true)->get()->each->delete();

            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists(SiteImages::path('example-ragdoll'))) {
            try {
                Artisan::call('catmarket:images');
            } catch (Throwable) {
            }
        }

        $user = User::query()->firstOrCreate(
            ['email' => self::EMAIL],
            ['name' => 'Petavilo examples', 'password' => bin2hex(random_bytes(24)), 'status' => 'active'],
        );

        foreach ($this->records() as $record) {
            if (Listing::query()->withTrashed()->where('is_example', true)->where('title', $record['title'])->exists()) {
                continue;
            }

            $category = Category::query()->where('slug', $record['category'])->first();

            if (! $category instanceof Category) {
                continue;
            }

            $listing = Listing::createFromFrontend([
                'title' => $record['title'],
                'description' => $record['description'],
                'price' => $record['price'],
                'currency' => $record['country'] === 'United States' ? 'USD' : 'GBP',
                'category_id' => (int) $category->getKey(),
                'custom_fields' => $record['fields'],
                'contact_email' => self::EMAIL,
                'country' => $record['country'],
                'city' => $record['city'],
                'latitude' => $record['lat'],
                'longitude' => $record['lng'],
                'expires_at' => now()->addYear(),
            ], $user->getKey());

            $listing->forceFill([
                'is_example' => true,
                'status' => 'active',
                'is_featured' => $record['featured'] ?? false,
                'is_urgent' => $record['urgent'] ?? false,
            ])->save();

            $path = $disk->path(SiteImages::path($record['image']));

            if (is_file($path)) {
                try {
                    $listing->attachListingImage($path, $record['image'].'.jpg');
                } catch (Throwable) {
                }
            }
        }
    }

    private function records(): array
    {
        $kittens = CatCatalog::childSlug('kittens', 'Pedigree kittens');
        $pedigree = CatCatalog::childSlug('pedigree-cats', 'Adult pedigree cats');
        $products = CatCatalog::childSlug('cat-products', 'Cat trees & furniture');
        $rehoming = CatCatalog::childSlug('cats-for-rehoming', 'Adult cats');
        $lost = CatCatalog::childSlug('lost-and-found', 'Lost cats');
        $sitting = CatCatalog::childSlug('cat-services', 'Cat sitting');

        return [
            [
                'title' => 'Ragdoll kittens, seal and blue point, GCCF registered',
                'category' => $kittens, 'image' => 'example-ragdoll', 'featured' => true,
                'country' => 'United Kingdom', 'city' => 'Leeds', 'lat' => 53.8008, 'lng' => -1.5491, 'price' => 950,
                'description' => "Two beautiful Ragdoll kittens from our second litter, raised in the house with children and a calm older cat.\n\nThey leave at 13 weeks fully vaccinated, microchipped, flea and worm treated, with 4 weeks' free insurance and a kitten pack. Mum and dad can both be seen.\n\nViewing by appointment. A deposit is only taken after you have met the kittens.",
                'fields' => ['seller_type' => CatCatalog::SELLER_REGISTERED_BREEDER, 'breed' => 'Ragdoll', 'date_of_birth' => now()->subWeeks(10)->toDateString(), 'sex' => 'Mixed litter', 'neutered' => 'Too young', 'vaccinations' => 'First vaccination', 'microchipped' => true, 'flea_worm_treated' => true, 'lifestyle' => 'Indoor only', 'good_with' => 'Children and other pets', 'ready_to_leave' => now()->addWeeks(3)->toDateString(), 'mother_seen' => true, 'registration' => 'GCCF'],
            ],
            [
                'title' => 'Bengal girl, 2 years, TICA registered, needs a calm home',
                'category' => $pedigree, 'image' => 'example-bengal',
                'country' => 'United States', 'city' => 'Austin, TX', 'lat' => 30.2672, 'lng' => -97.7431, 'price' => 400,
                'description' => "Luna is a spotted brown Bengal, spayed and up to date with her shots. She is very playful, loves her wheel and a window to watch birds.\n\nWe are moving overseas and cannot take her. Her cat tree, carrier and food come with her. Rehoming fee goes to her new vet costs if you need it back.",
                'fields' => ['seller_type' => CatCatalog::SELLER_PRIVATE, 'breed' => 'Bengal', 'date_of_birth' => now()->subYears(2)->toDateString(), 'sex' => 'Female', 'neutered' => 'Yes', 'vaccinations' => 'Boosters up to date', 'microchipped' => true, 'flea_worm_treated' => true, 'lifestyle' => 'Indoor only', 'good_with' => 'Adults only', 'registration' => 'TICA'],
            ],
            [
                'title' => 'Large cat tree, 150 cm, grey, hammock and cave',
                'category' => $products, 'image' => 'example-cat-tree',
                'country' => 'United Kingdom', 'city' => 'Manchester', 'lat' => 53.4808, 'lng' => -2.2426, 'price' => 35,
                'description' => "Five-level cat tree with sisal posts, a hammock, a cave and a top platform. About 18 months old, posts still in good condition, all fabric washed.\n\nCollection only from south Manchester. Comes apart into pieces to fit in a hatchback.",
                'fields' => ['condition' => 'Used - good', 'brand' => 'Unbranded'],
            ],
            [
                'title' => 'Grey tabby, 6 years, gentle lap cat looking for a quiet home',
                'category' => $rehoming, 'image' => 'rehoming', 'urgent' => true,
                'country' => 'United Kingdom', 'city' => 'Edinburgh', 'lat' => 55.9533, 'lng' => -3.1883, 'price' => 0,
                'description' => "Smokey is a neutered male who loves a lap and a sunny windowsill. He is house trained, fine with visitors and has always had outdoor access.\n\nMy new landlord does not allow pets, so I need to find him a home by the end of the month. Free to the right home, a home visit would be appreciated.",
                'fields' => ['seller_type' => CatCatalog::SELLER_PRIVATE, 'breed' => 'Domestic shorthair (moggy)', 'date_of_birth' => now()->subYears(6)->toDateString(), 'sex' => 'Male', 'neutered' => 'Yes', 'vaccinations' => 'Boosters up to date', 'microchipped' => true, 'flea_worm_treated' => true, 'lifestyle' => 'Outdoor access', 'good_with' => 'Adults only'],
            ],
            [
                'title' => 'LOST: black and white cat, Ballard area',
                'category' => $lost, 'image' => 'lost-found',
                'country' => 'United States', 'city' => 'Seattle, WA', 'lat' => 47.6686, 'lng' => -122.3860, 'price' => 0,
                'description' => "Our cat Oreo has not come home since Tuesday evening. He is black with a white chest, white paws and a small white patch on his nose. Friendly but nervous with strangers.\n\nPlease check sheds and garages. Message us any time with a sighting.",
                'fields' => ['date_lost_found' => now()->subDays(2)->toDateString(), 'area_last_seen' => 'NW 65th St near the park, Ballard', 'colour_markings' => 'Black with white chest, paws and nose patch', 'microchip_status' => 'Yes', 'wearing_collar' => false],
            ],
            [
                'title' => 'Cat sitting visits, 2 a day, insured, Brooklyn',
                'category' => $sitting, 'image' => 'services',
                'country' => 'United States', 'city' => 'New York, NY', 'lat' => 40.6782, 'lng' => -73.9442, 'price' => 25,
                'description' => "Daily home visits while you are away: feeding, fresh water, litter, play and a photo update each visit. I can give medication and look after older cats.\n\nPrice is per visit. Meet and greet before your first booking is free.",
                'fields' => ['area_covered' => 'Brooklyn and Lower Manhattan', 'insured' => true, 'qualifications' => 'Pet first aid certificate'],
            ],
        ];
    }
}
