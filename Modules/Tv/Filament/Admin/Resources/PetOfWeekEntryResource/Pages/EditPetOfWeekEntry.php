<?php

declare(strict_types=1);

namespace Modules\Tv\Filament\Admin\Resources\PetOfWeekEntryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Tv\Filament\Admin\Resources\PetOfWeekEntryResource;

class EditPetOfWeekEntry extends EditRecord
{
    protected static string $resource = PetOfWeekEntryResource::class;
}
