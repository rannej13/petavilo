<?php

declare(strict_types=1);

namespace Modules\Rescue\Filament\Admin\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Rescue\Filament\Admin\Resources\DonationResource\Pages;
use Modules\Rescue\Models\Donation;
use UnitEnum;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static string|UnitEnum|null $navigationGroup = 'Rescue';

    protected static ?string $label = 'Donation';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Ref')->formatStateUsing(fn (int $state): string => 'D'.str_pad((string) $state, 6, '0', STR_PAD_LEFT)),
                TextColumn::make('organisation.name')->label('Rescue')->searchable(),
                TextColumn::make('amount')->formatStateUsing(fn (Donation $record): string => $record->totalLabel()),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    Donation::STATUS_PAID => 'success',
                    Donation::STATUS_FAILED => 'danger',
                    default => 'warning',
                }),
                TextColumn::make('donor_name')->label('Donor'),
                IconColumn::make('is_anonymous')->boolean()->label('Private'),
                TextColumn::make('stripe_session_id')->label('Stripe session')->limit(16)->copyable(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([SelectFilter::make('status')->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListDonations::route('/')];
    }
}
