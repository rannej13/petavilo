<?php

declare(strict_types=1);

namespace Modules\Tv;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class TvPlugin implements Plugin
{
    public function getId(): string
    {
        return 'tv';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Tv', 'Filament/Admin/Resources'),
            for: 'Modules\\Tv\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
