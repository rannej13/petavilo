<?php

declare(strict_types=1);

namespace Modules\Rescue\Filament\Admin\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Rescue\Filament\Admin\Resources\AdoptionApplicationResource\Pages;
use Modules\Rescue\Models\AdoptionApplication;
use UnitEnum;

class AdoptionApplicationResource extends Resource
{
    protected static ?string $model = AdoptionApplication::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Rescue';

    protected static ?string $label = 'Adoption application';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#'),
                TextColumn::make('organisation.name')->label('Rescue')->searchable(),
                TextColumn::make('applicant.name')->label('Applicant'),
                TextColumn::make('listing_id')->label('Advert'),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => AdoptionApplication::statuses()[$state] ?? $state),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([SelectFilter::make('status')->options(AdoptionApplication::statuses())]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAdoptionApplications::route('/')];
    }
}
