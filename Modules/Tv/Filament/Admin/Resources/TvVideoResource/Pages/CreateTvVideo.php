<?php

declare(strict_types=1);

namespace Modules\Tv\Filament\Admin\Resources\TvVideoResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Tv\Filament\Admin\Resources\TvVideoResource;

class CreateTvVideo extends CreateRecord
{
    protected static string $resource = TvVideoResource::class;
}
