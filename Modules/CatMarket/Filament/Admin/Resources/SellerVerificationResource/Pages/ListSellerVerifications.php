<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource;

class ListSellerVerifications extends ListRecords
{
    protected static string $resource = SellerVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
