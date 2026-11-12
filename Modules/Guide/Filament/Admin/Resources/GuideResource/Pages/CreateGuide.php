<?php

declare(strict_types=1);

namespace Modules\Guide\Filament\Admin\Resources\GuideResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Guide\Filament\Admin\Resources\GuideResource;

class CreateGuide extends CreateRecord
{
    protected static string $resource = GuideResource::class;
}
