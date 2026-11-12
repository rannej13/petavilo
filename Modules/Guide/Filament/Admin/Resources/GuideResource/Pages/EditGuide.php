<?php

declare(strict_types=1);

namespace Modules\Guide\Filament\Admin\Resources\GuideResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Guide\Filament\Admin\Resources\GuideResource;

class EditGuide extends EditRecord
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
