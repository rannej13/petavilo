<?php

declare(strict_types=1);

namespace Modules\Guide\Filament\Admin\Resources\GuideResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Guide\Filament\Admin\Resources\GuideResource;

class ListGuides extends ListRecords
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
