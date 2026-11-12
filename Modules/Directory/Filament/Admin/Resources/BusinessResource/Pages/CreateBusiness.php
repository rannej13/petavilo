<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources\BusinessResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Directory\Filament\Admin\Resources\BusinessResource;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;
}
