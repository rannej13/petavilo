<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\ThreadResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Community\Filament\Admin\Resources\ThreadResource;

class CreateThread extends CreateRecord
{
    protected static string $resource = ThreadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['user_id' => auth()->id()];
    }
}
