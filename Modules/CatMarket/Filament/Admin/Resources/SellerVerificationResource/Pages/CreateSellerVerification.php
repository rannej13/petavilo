<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource;

class CreateSellerVerification extends CreateRecord
{
    protected static string $resource = SellerVerificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SellerVerificationResource::mutateVerifier($data);
    }
}
