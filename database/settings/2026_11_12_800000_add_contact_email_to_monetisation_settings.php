<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('monetisation.contact_email')) {
            $this->migrator->add('monetisation.contact_email', null);
        }
    }
};
