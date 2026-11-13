<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('monetisation.show_example_adverts')) {
            $this->migrator->add('monetisation.show_example_adverts', true);
        }
    }
};
