<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources\BusinessResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Directory\Filament\Admin\Resources\BusinessResource;

class ListBusinesses extends ListRecords
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
