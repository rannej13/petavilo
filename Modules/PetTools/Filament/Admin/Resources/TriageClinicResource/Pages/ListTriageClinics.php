<?php

declare(strict_types=1);

namespace Modules\PetTools\Filament\Admin\Resources\TriageClinicResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\PetTools\Filament\Admin\Resources\TriageClinicResource;

class ListTriageClinics extends ListRecords
{
    protected static string $resource = TriageClinicResource::class;
}
