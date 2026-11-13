<?php

declare(strict_types=1);

namespace Modules\CatMarket\Settings;

use Spatie\LaravelSettings\Settings;

class MonetisationSettings extends Settings
{
    public ?string $stripe_key;

    public ?string $stripe_secret;

    public ?string $stripe_webhook_secret;

    public ?string $stripe_price_breeder;

    public ?string $stripe_price_breeder_usd;

    public ?string $stripe_price_directory_plus;

    public ?string $stripe_price_directory_plus_usd;

    public ?string $stripe_price_directory_premium;

    public ?string $stripe_price_directory_premium_usd;

    public ?string $adsense_client;

    public ?string $adsense_slot_home;

    public ?string $adsense_slot_listing_infeed;

    public ?string $adsense_slot_listing_detail;

    public ?string $adsense_slot_guide_top;

    public ?string $adsense_slot_guide_bottom;

    public ?string $adsense_slot_directory;

    public ?string $amazon_tag_uk;

    public ?string $amazon_tag_us;

    public ?string $contact_email;

    public ?string $mail_host;

    public ?string $mail_port;

    public ?string $mail_username;

    public ?string $mail_password;

    public ?string $mail_from_address;

    public ?string $televet_name_uk;

    public ?string $televet_url_uk;

    public ?string $televet_name_us;

    public ?string $televet_url_us;

    public ?string $insurers_uk;

    public ?string $insurers_us;

    public ?string $stripe_price_clinic;

    public ?string $stripe_price_clinic_usd;

    public static function group(): string
    {
        return 'monetisation';
    }

    public static function encrypted(): array
    {
        return ['stripe_secret', 'stripe_webhook_secret', 'mail_password'];
    }
}
