<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Site\App\Settings\GeneralSettings;
use Throwable;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $settings = app(GeneralSettings::class);
        } catch (Throwable) {
            $this->command?->warn('General settings are not migrated yet.');

            return;
        }

        $name = (string) config('catmarket.site_name');
        $email = (string) config('catmarket.company.email');

        $settings->site_name = $name;
        $settings->site_description = (string) config('catmarket.site_tagline');
        $settings->default_language = 'en';
        $settings->default_country_code = '+44';
        $settings->currencies = ['GBP', 'USD'];
        $settings->sender_name = $name;

        if ($email !== '') {
            $settings->sender_email = $email;
        }

        $settings->home_slides = [
            [
                'badge' => 'Rehoming',
                'title' => 'Find a cat who needs a home near you.',
                'subtitle' => 'Microchip, vaccination and seller details on every cat advert.',
                'primary_button_text' => 'Browse cats',
                'secondary_button_text' => 'Rehome a cat',
                'image_path' => 'images/home-slides/slide-marketplace.svg',
            ],
            [
                'badge' => 'Kittens',
                'title' => 'Kittens from people who bred them, shown with their mother.',
                'subtitle' => 'Licence numbers, microchips and vaccinations listed on every advert.',
                'primary_button_text' => 'See kittens',
                'secondary_button_text' => 'Advertise a litter',
                'image_path' => 'images/home-slides/slide-categories.svg',
            ],
            [
                'badge' => 'Cat services',
                'title' => 'Trusted vets, sitters, groomers and catteries.',
                'subtitle' => 'Search the directory or list your cat business for free.',
                'primary_button_text' => 'Open directory',
                'secondary_button_text' => 'List your business',
                'image_path' => 'images/home-slides/slide-local.svg',
            ],
        ];

        $settings->save();
    }
}
