<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\BoardResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Admin\Resources\BoardResource;

class ListBoards extends ListRecords
{
    protected static string $resource = BoardResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
