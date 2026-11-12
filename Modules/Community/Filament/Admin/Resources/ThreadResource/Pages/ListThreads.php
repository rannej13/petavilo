<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\ThreadResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Admin\Resources\ThreadResource;

class ListThreads extends ListRecords
{
    protected static string $resource = ThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
