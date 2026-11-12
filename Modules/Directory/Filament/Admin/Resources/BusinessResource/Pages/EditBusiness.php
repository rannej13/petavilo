<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources\BusinessResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Directory\Filament\Admin\Resources\BusinessResource;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
