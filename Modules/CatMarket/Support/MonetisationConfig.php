<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Modules\CatMarket\Settings\MonetisationSettings;
use Throwable;

final class MonetisationConfig
{
    public static function apply(): void
    {
        try {
            $settings = app(MonetisationSettings::class);
        } catch (Throwable) {
            return;
        }

        $map = [
            'cashier.key' => $settings->stripe_key,
            'cashier.secret' => $settings->stripe_secret,
            'cashier.webhook.secret' => $settings->stripe_webhook_secret,
            'billing.memberships.breeder.price_id' => $settings->stripe_price_breeder,
            'billing.memberships.breeder.price_id_usd' => $settings->stripe_price_breeder_usd,
            'billing.memberships.directory-plus.price_id' => $settings->stripe_price_directory_plus,
            'billing.memberships.directory-plus.price_id_usd' => $settings->stripe_price_directory_plus_usd,
            'billing.memberships.directory-premium.price_id' => $settings->stripe_price_directory_premium,
            'billing.memberships.directory-premium.price_id_usd' => $settings->stripe_price_directory_premium_usd,
            'ads.client' => $settings->adsense_client,
            'ads.slots.home' => $settings->adsense_slot_home,
            'ads.slots.listing_infeed' => $settings->adsense_slot_listing_infeed,
            'ads.slots.listing_detail' => $settings->adsense_slot_listing_detail,
            'ads.slots.guide_top' => $settings->adsense_slot_guide_top,
            'ads.slots.guide_bottom' => $settings->adsense_slot_guide_bottom,
            'ads.slots.directory' => $settings->adsense_slot_directory,
            'catmarket.amazon.uk' => $settings->amazon_tag_uk,
            'catmarket.amazon.us' => $settings->amazon_tag_us,
        ];

        foreach ($map as $key => $value) {
            if (is_string($value) && trim($value) !== '') {
                config([$key => trim($value)]);
            }
        }
    }
}
