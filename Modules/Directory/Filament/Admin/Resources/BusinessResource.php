<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Directory\Filament\Admin\Resources\BusinessResource\Pages;
use Modules\Directory\Models\Business;
use UnitEnum;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Directory';

    protected static ?string $label = 'Business';

    protected static ?string $pluralLabel = 'Businesses';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Business::query()->where('is_published', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(120),
            Select::make('type')->options(Business::types())->required(),
            Select::make('country')->options(Business::countries())->default('GB')->required(),
            TextInput::make('town')->required()->maxLength(80),
            TextInput::make('postcode')->maxLength(12),
            TextInput::make('address_line')->maxLength(160),
            TextInput::make('phone')->tel()->maxLength(40),
            TextInput::make('email')->email()->maxLength(160),
            TextInput::make('website')->maxLength(200),
            TextInput::make('licence_number')->maxLength(80),
            Textarea::make('description')->rows(5)->maxLength(3000)->columnSpanFull(),
            Textarea::make('opening_hours')->rows(3)->maxLength(600),
            Select::make('owner_user_id')->label('Owner')->relationship('owner', 'email')->searchable()->preload(),
            Select::make('tier')->options(['basic' => 'Basic', 'plus' => 'Plus', 'premium' => 'Premium'])->default('basic')
                ->helperText('Synced from the owner\'s membership every night. Set by hand only for sponsors without a subscription.'),
            Select::make('accreditation')->options(Business::ACCREDITATIONS)->placeholder('None'),
            Toggle::make('accreditation_verified')->label('Accreditation checked')
                ->helperText('Check the official list (catfriendlyclinic.org for the UK, catfriendly.com for the US) before turning this on.'),
            Toggle::make('emergency_care'),
            Toggle::make('home_visits'),
            Toggle::make('cat_only'),
            TextInput::make('booking_url')->maxLength(200),
            TextInput::make('new_client_offer')->maxLength(160),
            Toggle::make('is_verified')->label('Verified'),
            Toggle::make('is_published')->label('Published'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->formatStateUsing(fn (string $state): string => Business::typeName($state))->badge(),
                TextColumn::make('country')->badge()->sortable(),
                TextColumn::make('town')->searchable()->sortable(),
                TextColumn::make('tier')->badge()->sortable(),
                IconColumn::make('is_verified')->boolean()->label('Verified'),
                IconColumn::make('is_published')->boolean()->label('Live'),
                TextColumn::make('view_count')->label('Views')->sortable(),
                TextColumn::make('website_clicks')->label('Clicks')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('invited_at')->label('Invited')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
                SelectFilter::make('type')->options(Business::types()),
                SelectFilter::make('country')->options(Business::countries()),
                SelectFilter::make('tier')->options(['basic' => 'Basic', 'plus' => 'Plus', 'premium' => 'Premium']),
                Filter::make('accreditation_to_check')->label('Accreditation to check')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('accreditation')->where('accreditation_verified', false)),
                TernaryFilter::make('claimed')->label('Claimed')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('owner_user_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('owner_user_id'),
                    ),
                Filter::make('invited')->label('Invited by email')->query(fn (Builder $query): Builder => $query->whereNotNull('invited_at')),
            ])
            ->actions([
                Action::make('verifyAccreditation')
                    ->label('Accreditation OK')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Business $record): bool => filled($record->getAttribute('accreditation')) && ! $record->getAttribute('accreditation_verified'))
                    ->action(fn (Business $record): bool => $record->forceFill(['accreditation_verified' => true])->save()),
                ...ResourceTableActions::editDelete(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
