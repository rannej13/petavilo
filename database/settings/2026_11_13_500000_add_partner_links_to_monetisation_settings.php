<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['televet_name_uk', 'televet_url_uk', 'televet_name_us', 'televet_url_us', 'insurers_uk', 'insurers_us', 'stripe_price_clinic', 'stripe_price_clinic_usd'] as $key) {
            if (! $this->migrator->exists('monetisation.'.$key)) {
                $this->migrator->add('monetisation.'.$key, null);
            }
        }
    }
};
