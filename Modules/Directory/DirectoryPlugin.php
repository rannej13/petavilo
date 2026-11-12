<?php

declare(strict_types=1);

namespace Modules\Directory;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class DirectoryPlugin implements Plugin
{
    public function getId(): string
    {
        return 'directory';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Directory', 'Filament/Admin/Resources'),
            for: 'Modules\\Directory\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
