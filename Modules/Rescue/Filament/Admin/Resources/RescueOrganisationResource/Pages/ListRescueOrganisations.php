<?php

declare(strict_types=1);

namespace Modules\Rescue\Filament\Admin\Resources\RescueOrganisationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Rescue\Filament\Admin\Resources\RescueOrganisationResource;

class ListRescueOrganisations extends ListRecords
{
    protected static string $resource = RescueOrganisationResource::class;
}
