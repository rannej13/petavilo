<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['enable_microsoft_login' => false, 'microsoft_client_id' => null, 'microsoft_client_secret' => null] as $key => $default) {
            if (! $this->migrator->exists('general.'.$key)) {
                $this->migrator->add('general.'.$key, $default);
            }
        }
    }
};
