<?php

declare(strict_types=1);

namespace Modules\Tv\Filament\Admin\Resources\TvVideoResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Tv\Filament\Admin\Resources\TvVideoResource;

class ListTvVideos extends ListRecords
{
    protected static string $resource = TvVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
