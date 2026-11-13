<?php

declare(strict_types=1);

namespace Modules\Rescue\Providers;

use App\Support\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Rescue\Models\RescueOrganisation;

class RescueServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Rescue', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('Rescue', 'routes/web.php'));
        $this->loadViewsFrom(module_path('Rescue', 'resources/views'), 'rescue');
        $this->loadTranslationsFrom(module_path('Rescue', 'lang'), 'rescue');

        SitemapRegistry::register(static fn (): array => [
            ['url' => route('rescue.adopt')],
            ['url' => route('rescue.rehome')],
            ['url' => route('rescue.lost-found')],
            ['url' => route('rescue.found-animal')],
            ['url' => route('rescue.foster')],
            ['url' => route('rescue.rescues')],
            ['url' => route('rescue.welfare')],
            ...RescueOrganisation::query()->verified()->get(['slug'])->map(static fn (RescueOrganisation $organisation): array => [
                'url' => route('rescue.show', $organisation->getAttribute('slug')),
            ])->all(),
        ]);
    }
}
