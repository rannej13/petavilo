<?php

declare(strict_types=1);

namespace Modules\CatMarket;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class CatMarketPlugin implements Plugin
{
    public function getId(): string
    {
        return 'catmarket';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('CatMarket', 'Filament/Admin/Resources'),
            for: 'Modules\\CatMarket\\Filament\\Admin\\Resources',
        );

        $panel->discoverPages(
            in: module_path('CatMarket', 'Filament/Admin/Pages'),
            for: 'Modules\\CatMarket\\Filament\\Admin\\Pages',
        );
    }

    public function boot(Panel $panel): void {}
}
