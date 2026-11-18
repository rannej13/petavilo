<?php

declare(strict_types=1);

namespace Modules\Tv\Filament\Admin\Resources\PetOfWeekEntryResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Tv\Filament\Admin\Resources\PetOfWeekEntryResource;

class ListPetOfWeekEntries extends ListRecords
{
    protected static string $resource = PetOfWeekEntryResource::class;
}
