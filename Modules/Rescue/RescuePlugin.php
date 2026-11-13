<?php

declare(strict_types=1);

namespace Modules\Rescue;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class RescuePlugin implements Plugin
{
    public function getId(): string
    {
        return 'rescue';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Rescue', 'Filament/Admin/Resources'),
            for: 'Modules\\Rescue\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
