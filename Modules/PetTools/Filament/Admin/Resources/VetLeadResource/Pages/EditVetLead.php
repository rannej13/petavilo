<?php

declare(strict_types=1);

namespace Modules\PetTools\Filament\Admin\Resources\VetLeadResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\PetTools\Filament\Admin\Resources\VetLeadResource;

class EditVetLead extends EditRecord
{
    protected static string $resource = VetLeadResource::class;
}
