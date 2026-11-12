<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Resources\AnimalListingReviewResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\CatMarket\Filament\Admin\Resources\AnimalListingReviewResource;

class ListAnimalListingReviews extends ListRecords
{
    protected static string $resource = AnimalListingReviewResource::class;
}
