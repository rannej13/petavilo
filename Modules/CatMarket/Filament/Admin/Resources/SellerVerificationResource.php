<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\CatMarket\Filament\Admin\Resources\SellerVerificationResource\Pages;
use Modules\CatMarket\Models\SellerVerification;
use UnitEnum;

class SellerVerificationResource extends Resource
{
    protected static ?string $model = SellerVerification::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $label = 'Seller verification';

    protected static ?string $pluralLabel = 'Seller verifications';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('user_id')
                ->relationship('user', 'email')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('kind')->options(SellerVerification::kinds())->required(),
            TextInput::make('reference')
                ->label('Licence, registration or charity number')
                ->maxLength(120),
            TextInput::make('issuing_authority')
                ->label('Issued by')
                ->helperText('Council, GCCF, TICA or Charity Commission')
                ->maxLength(160),
            DatePicker::make('expires_on')->label('Licence expires'),
            DateTimePicker::make('verified_at')
                ->label('Verified at')
                ->helperText('Set this only after checking the public register.')
                ->default(now()),
            Textarea::make('note')->rows(3)->maxLength(1000),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('Seller')->searchable(),
                TextColumn::make('kind')->formatStateUsing(fn (string $state): string => SellerVerification::kinds()[$state] ?? $state)->badge(),
                TextColumn::make('reference')->searchable(),
                TextColumn::make('issuing_authority')->label('Issued by'),
                TextColumn::make('expires_on')->date()->sortable(),
                TextColumn::make('verified_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([SelectFilter::make('kind')->options(SellerVerification::kinds())])
            ->actions(ResourceTableActions::editDelete());
    }

    public static function mutateVerifier(array $data): array
    {
        if (filled($data['verified_at'] ?? null)) {
            $data['verified_by'] = auth()->id();
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellerVerifications::route('/'),
            'create' => Pages\CreateSellerVerification::route('/create'),
            'edit' => Pages\EditSellerVerification::route('/{record}/edit'),
        ];
    }
}
