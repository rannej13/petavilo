<?php

declare(strict_types=1);

namespace Modules\Rescue\Filament\Admin\Resources\AdoptionApplicationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Rescue\Filament\Admin\Resources\AdoptionApplicationResource;

class ListAdoptionApplications extends ListRecords
{
    protected static string $resource = AdoptionApplicationResource::class;
}
