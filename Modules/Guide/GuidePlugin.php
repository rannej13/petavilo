<?php

declare(strict_types=1);

namespace Modules\Guide;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class GuidePlugin implements Plugin
{
    public function getId(): string
    {
        return 'guide';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Guide', 'Filament/Admin/Resources'),
            for: 'Modules\\Guide\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
