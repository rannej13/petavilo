<?php

declare(strict_types=1);

namespace Modules\PetTools;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class PetToolsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'pettools';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('PetTools', 'Filament/Admin/Resources'),
            for: 'Modules\\PetTools\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
