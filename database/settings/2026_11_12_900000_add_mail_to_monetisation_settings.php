<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['mail_host', 'mail_port', 'mail_username', 'mail_from_address'] as $key) {
            if (! $this->migrator->exists('monetisation.'.$key)) {
                $this->migrator->add('monetisation.'.$key, null);
            }
        }

        if (! $this->migrator->exists('monetisation.mail_password')) {
            $this->migrator->addEncrypted('monetisation.mail_password', null);
        }
    }
};
