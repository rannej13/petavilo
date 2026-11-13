<?php

declare(strict_types=1);

namespace Modules\Rescue\Filament\Admin\Resources\DonationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Rescue\Filament\Admin\Resources\DonationResource;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;
}
