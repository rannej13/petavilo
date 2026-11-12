<?php

declare(strict_types=1);

namespace Modules\Community;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class CommunityPlugin implements Plugin
{
    public function getId(): string
    {
        return 'community';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: module_path('Community', 'Filament/Admin/Resources'),
            for: 'Modules\\Community\\Filament\\Admin\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
