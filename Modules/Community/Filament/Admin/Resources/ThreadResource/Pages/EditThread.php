<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\ThreadResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Admin\Resources\ThreadResource;

class EditThread extends EditRecord
{
    protected static string $resource = ThreadResource::class;

}
