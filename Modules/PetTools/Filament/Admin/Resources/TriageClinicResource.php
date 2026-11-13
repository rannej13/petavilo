<?php

declare(strict_types=1);

namespace Modules\PetTools\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\PetTools\Filament\Admin\Resources\TriageClinicResource\Pages;
use Modules\PetTools\Models\TriageClinic;
use UnitEnum;

class TriageClinicResource extends Resource
{
    protected static ?string $model = TriageClinic::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Pet tools';

    protected static ?string $label = 'Clinic widget';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('country'),
                TextColumn::make('trial_ends_at')->label('Trial ends')->date(),
                IconColumn::make('subscribed')->label('Paying')->boolean()->state(fn (TriageClinic $record): bool => $record->isSubscribed()),
                IconColumn::make('is_suspended')->label('Suspended')->boolean(),
                TextColumn::make('submissions_count')->counts('submissions')->label('Triages'),
                TextColumn::make('created_at')->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('toggle')
                    ->label(fn (TriageClinic $record): string => $record->getAttribute('is_suspended') ? 'Unsuspend' : 'Suspend')
                    ->requiresConfirmation()
                    ->action(fn (TriageClinic $record): bool => $record->forceFill(['is_suspended' => ! $record->getAttribute('is_suspended')])->save()),
                Action::make('extend')
                    ->label('Extend trial 14 days')
                    ->action(fn (TriageClinic $record): bool => $record->forceFill(['trial_ends_at' => now()->max($record->getAttribute('trial_ends_at') ?? now())->addDays(14)])->save()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTriageClinics::route('/')];
    }
}
