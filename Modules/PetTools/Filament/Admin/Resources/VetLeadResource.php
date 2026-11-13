<?php

declare(strict_types=1);

namespace Modules\PetTools\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\PetTools\Filament\Admin\Resources\VetLeadResource\Pages;
use Modules\PetTools\Models\VetLead;
use UnitEnum;

class VetLeadResource extends Resource
{
    protected static ?string $model = VetLead::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|UnitEnum|null $navigationGroup = 'Pet tools';

    protected static ?string $label = 'Vet check request';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = VetLead::query()->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('phone')->disabled(),
            TextInput::make('town')->disabled(),
            TextInput::make('pet_name')->disabled(),
            TextInput::make('summary')->disabled()->columnSpanFull(),
            Select::make('status')->options(VetLead::statuses())->required(),
            Textarea::make('admin_note')->label('Notes (which vet it was sent to, outcome)')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#'),
                TextColumn::make('kind')->badge(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('town')->searchable(),
                TextColumn::make('country'),
                TextColumn::make('summary')->limit(40),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => VetLead::statuses()[$state] ?? $state),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(VetLead::statuses()),
                SelectFilter::make('kind')->options(['dental' => 'Dental', 'senior' => 'Senior']),
            ])
            ->actions(ResourceTableActions::editDelete());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVetLeads::route('/'),
            'edit' => Pages\EditVetLead::route('/{record}/edit'),
        ];
    }
}
