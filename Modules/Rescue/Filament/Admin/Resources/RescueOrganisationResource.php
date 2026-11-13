<?php

declare(strict_types=1);

namespace Modules\Rescue\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Rescue\Filament\Admin\Resources\RescueOrganisationResource\Pages;
use Modules\Rescue\Models\RescueOrganisation;
use UnitEnum;

class RescueOrganisationResource extends Resource
{
    protected static ?string $model = RescueOrganisation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Rescue';

    protected static ?string $label = 'Rescue';

    protected static ?string $pluralLabel = 'Rescues';

    public static function getNavigationBadge(): ?string
    {
        $count = RescueOrganisation::query()->where('status', RescueOrganisation::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(120),
            Select::make('country')->options(['GB' => 'United Kingdom', 'US' => 'United States'])->required(),
            TextInput::make('town')->maxLength(80),
            TextInput::make('charity_number')->label('Charity number or EIN')->maxLength(40)
                ->helperText('Check on the Charity Commission, OSCR, NI Charity Commission or IRS Tax Exempt Organization Search before verifying.'),
            TextInput::make('website')->url()->maxLength(200),
            TextInput::make('contact_email')->email()->maxLength(160),
            CheckboxList::make('species')->options(RescueOrganisation::speciesOptions())->columns(4),
            Textarea::make('description')->rows(6)->columnSpanFull(),
            Toggle::make('accepts_fosters'),
            Toggle::make('takes_rehoming_requests'),
            Toggle::make('donations_enabled')->label('Allow donations')->helperText('Switch off to pause the Donate button for this rescue.'),
            TextInput::make('stripe_account_id')->label('Stripe account')->disabled()->dehydrated(false),
            Toggle::make('payouts_ready')->label('Stripe ready for payments')->disabled()->dehydrated(false),
            Textarea::make('evidence_note')->label('Verification notes (admin only)')->rows(3)->columnSpanFull()
                ->helperText('What you checked and when, for example: Charity Commission 1234567 checked 13 Nov 2026, name and address match.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->wrap(),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => RescueOrganisation::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        RescueOrganisation::STATUS_VERIFIED => 'success',
                        RescueOrganisation::STATUS_PENDING => 'warning',
                        RescueOrganisation::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('charity_number')->label('Charity / EIN')->copyable(),
                TextColumn::make('country'),
                TextColumn::make('town'),
                TextColumn::make('user.email')->label('Account'),
                \Filament\Tables\Columns\IconColumn::make('payouts_ready')->boolean()->label('Donations'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(RescueOrganisation::statuses()),
                SelectFilter::make('country')->options(['GB' => 'United Kingdom', 'US' => 'United States']),
            ])
            ->actions([
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Only verify after checking the charity or nonprofit registration. The rescue gets a Verified badge and appears on the Adopt page.')
                    ->visible(fn (RescueOrganisation $record): bool => ! $record->isVerified())
                    ->action(fn (RescueOrganisation $record) => $record->approve((int) auth()->id())),
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (RescueOrganisation $record): bool => $record->getAttribute('status') !== RescueOrganisation::STATUS_SUSPENDED)
                    ->action(fn (RescueOrganisation $record) => $record->suspend()),
                Action::make('view')
                    ->label('Page')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (RescueOrganisation $record): string => route('rescue.show', $record->getAttribute('slug')), true),
                ...ResourceTableActions::editDelete(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRescueOrganisations::route('/'),
            'edit' => Pages\EditRescueOrganisation::route('/{record}/edit'),
        ];
    }
}
