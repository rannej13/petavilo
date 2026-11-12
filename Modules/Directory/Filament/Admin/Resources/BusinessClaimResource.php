<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Directory\Filament\Admin\Resources\BusinessClaimResource\Pages;
use Modules\Directory\Models\BusinessClaim;
use UnitEnum;

class BusinessClaimResource extends Resource
{
    protected static ?string $model = BusinessClaim::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';

    protected static string|UnitEnum|null $navigationGroup = 'Directory';

    protected static ?string $label = 'Business claim';

    protected static ?string $pluralLabel = 'Business claims';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = BusinessClaim::query()->where('status', BusinessClaim::PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business.name')->label('Business')->searchable(),
                TextColumn::make('user.email')->label('Claimed by')->searchable(),
                TextColumn::make('role_at_business')->label('Role'),
                TextColumn::make('phone'),
                TextColumn::make('message')->wrap()->limit(120),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    BusinessClaim::PENDING => 'Pending',
                    BusinessClaim::APPROVED => 'Approved',
                    BusinessClaim::REJECTED => 'Rejected',
                ])->default(BusinessClaim::PENDING),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Call the business on its public phone number to confirm this person works there before approving.')
                    ->visible(fn (BusinessClaim $record): bool => $record->getAttribute('status') === BusinessClaim::PENDING)
                    ->action(fn (BusinessClaim $record): mixed => $record->approve()),
                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (BusinessClaim $record): bool => $record->getAttribute('status') === BusinessClaim::PENDING)
                    ->action(fn (BusinessClaim $record): mixed => $record->reject()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessClaims::route('/'),
        ];
    }
}
