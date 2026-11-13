<?php

declare(strict_types=1);

namespace Modules\PetTools\Filament\Admin\Resources\VetLeadResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\PetTools\Filament\Admin\Resources\VetLeadResource;

class ListVetLeads extends ListRecords
{
    protected static string $resource = VetLeadResource::class;
}
