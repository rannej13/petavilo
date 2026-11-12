<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'stripe_key', 'stripe_price_breeder', 'stripe_price_breeder_usd', 'stripe_price_directory_plus',
            'stripe_price_directory_plus_usd', 'stripe_price_directory_premium', 'stripe_price_directory_premium_usd',
            'adsense_client', 'adsense_slot_home', 'adsense_slot_listing_infeed', 'adsense_slot_listing_detail',
            'adsense_slot_guide_top', 'adsense_slot_guide_bottom', 'adsense_slot_directory',
            'amazon_tag_uk', 'amazon_tag_us',
        ] as $key) {
            if (! $this->migrator->exists('monetisation.'.$key)) {
                $this->migrator->add('monetisation.'.$key, null);
            }
        }

        foreach (['stripe_secret', 'stripe_webhook_secret'] as $key) {
            if (! $this->migrator->exists('monetisation.'.$key)) {
                $this->migrator->addEncrypted('monetisation.'.$key, null);
            }
        }
    }
};
