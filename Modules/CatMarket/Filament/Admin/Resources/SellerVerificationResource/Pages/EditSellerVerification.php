<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource;

class EditSellerVerification extends EditRecord
{
    protected static string $resource = SellerVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SellerVerificationResource::mutateVerifier($data);
    }
}
