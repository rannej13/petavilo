<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources\BusinessClaimResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Directory\Filament\Admin\Resources\BusinessClaimResource;

class ListBusinessClaims extends ListRecords
{
    protected static string $resource = BusinessClaimResource::class;
}
