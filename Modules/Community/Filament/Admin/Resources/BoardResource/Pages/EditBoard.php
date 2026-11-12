<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\BoardResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Community\Filament\Admin\Resources\BoardResource;

class EditBoard extends EditRecord
{
    protected static string $resource = BoardResource::class;

}
