<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Illuminate\Support\Facades\Schema;
use Modules\CatMarket\Settings\MonetisationSettings;
use Throwable;

final class MonetisationConfig
{
    public static function apply(): void
    {
        try {
            self::applySettings();
        } catch (Throwable) {
            return;
        }
    }

    private static function applySettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $settings = app(MonetisationSettings::class);

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
            'billing.memberships.clinic-triage.price_id' => $settings->stripe_price_clinic,
            'billing.memberships.clinic-triage.price_id_usd' => $settings->stripe_price_clinic_usd,
            'ads.client' => $settings->adsense_client,
            'ads.slots.home' => $settings->adsense_slot_home,
            'ads.slots.listing_infeed' => $settings->adsense_slot_listing_infeed,
            'ads.slots.listing_detail' => $settings->adsense_slot_listing_detail,
            'ads.slots.guide_top' => $settings->adsense_slot_guide_top,
            'ads.slots.guide_bottom' => $settings->adsense_slot_guide_bottom,
            'ads.slots.directory' => $settings->adsense_slot_directory,
            'catmarket.amazon.uk' => $settings->amazon_tag_uk,
            'catmarket.amazon.us' => $settings->amazon_tag_us,
            'catmarket.company.email' => $settings->contact_email,
            'directory.outreach.from_address' => $settings->contact_email,
        ];

        foreach ($map as $key => $value) {
            if (is_string($value) && trim($value) !== '') {
                config([$key => trim($value)]);
            }
        }

        try {
            config(['catmarket.show_examples' => (bool) $settings->show_example_adverts]);
        } catch (Throwable) {
        }

        self::applyMail($settings);
    }

    private static function applyMail(MonetisationSettings $settings): void
    {
        $host = trim((string) ($settings->mail_host ?? ''));

        if ($host === '') {
            return;
        }

        $port = (int) ($settings->mail_port ?: 587);
        $from = trim((string) ($settings->mail_from_address ?: $settings->contact_email ?: ''));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.scheme' => $port === 465 ? 'smtps' : 'smtp',
            'mail.mailers.smtp.username' => trim((string) ($settings->mail_username ?? '')),
            'mail.mailers.smtp.password' => (string) ($settings->mail_password ?? ''),
        ]);

        if ($from !== '') {
            config(['mail.from.address' => $from, 'mail.from.name' => (string) config('app.name')]);
        }

        if (app()->resolved('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
    }
}
