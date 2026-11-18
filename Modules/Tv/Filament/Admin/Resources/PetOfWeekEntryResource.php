<?php

declare(strict_types=1);

namespace Modules\Tv\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Notification\Models\UserNotification;
use Modules\Tv\Filament\Admin\Resources\PetOfWeekEntryResource\Pages;
use Modules\Tv\Models\PetOfWeekEntry;
use UnitEnum;

class PetOfWeekEntryResource extends Resource
{
    protected static ?string $model = PetOfWeekEntry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|UnitEnum|null $navigationGroup = 'Petavilo TV';

    protected static ?string $label = 'Pet of the Week entry';

    protected static ?string $pluralLabel = 'Pet of the Week entries';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = PetOfWeekEntry::query()->where('status', PetOfWeekEntry::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('pet_name')->required()->maxLength(60),
            TextInput::make('caption')->maxLength(200),
            Select::make('status')->options(PetOfWeekEntry::statuses())->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')->disk('public')->label('Photo')->square()->size(64),
                TextColumn::make('pet_name')->searchable(),
                TextColumn::make('user.name')->label('Owner'),
                TextColumn::make('week_start')->date('j M Y')->sortable(),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => PetOfWeekEntry::statuses()[$state] ?? $state),
                TextColumn::make('votes_count')->label('Votes')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([SelectFilter::make('status')->options(PetOfWeekEntry::statuses())])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PetOfWeekEntry $record): bool => $record->getAttribute('status') === PetOfWeekEntry::STATUS_PENDING)
                    ->action(function (PetOfWeekEntry $record): void {
                        $record->forceFill(['status' => PetOfWeekEntry::STATUS_APPROVED, 'decided_at' => now()])->save();
                        self::tell($record, $record->getAttribute('pet_name').' is in the running', 'Share the Pet of the Week page with friends so they can vote. Voting closes on Sunday night.');
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PetOfWeekEntry $record): bool => $record->getAttribute('status') === PetOfWeekEntry::STATUS_PENDING)
                    ->action(function (PetOfWeekEntry $record): void {
                        $record->forceFill(['status' => PetOfWeekEntry::STATUS_REJECTED, 'decided_at' => now()])->save();
                        self::tell($record, 'Your Pet of the Week photo was not accepted', 'We could not use this photo. Common reasons: it is unclear, shows more than one person, or the pet looks stressed. You can enter a different photo next week.');
                    }),
                ...ResourceTableActions::editDelete(),
            ]);
    }

    private static function tell(PetOfWeekEntry $entry, string $title, string $body): void
    {
        try {
            UserNotification::publish((int) $entry->getAttribute('user_id'), UserNotification::TYPE_PROMOTION, $title, $body, route('tv.potw'));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPetOfWeekEntries::route('/'),
            'edit' => Pages\EditPetOfWeekEntry::route('/{record}/edit'),
        ];
    }
}
