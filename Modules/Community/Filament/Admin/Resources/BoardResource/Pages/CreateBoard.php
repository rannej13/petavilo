<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\BoardResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Community\Filament\Admin\Resources\BoardResource;

class CreateBoard extends CreateRecord
{
    protected static string $resource = BoardResource::class;

}
