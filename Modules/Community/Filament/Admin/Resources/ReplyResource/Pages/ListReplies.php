<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources\ReplyResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Community\Filament\Admin\Resources\ReplyResource;

class ListReplies extends ListRecords
{
    protected static string $resource = ReplyResource::class;

}
