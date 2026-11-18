<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('monetisation.rescue_donations_enabled')) {
            $this->migrator->add('monetisation.rescue_donations_enabled', false);
        }
    }
};
