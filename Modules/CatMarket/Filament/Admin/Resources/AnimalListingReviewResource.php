<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Resources;

use App\Support\ListingDirectory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CatMarket\Filament\Admin\Resources\AnimalListingReviewResource\Pages;
use Modules\CatMarket\Models\AnimalListingReview;
use Modules\CatMarket\Support\AnimalReviewDecision;
use UnitEnum;

class AnimalListingReviewResource extends Resource
{
    protected static ?string $model = AnimalListingReview::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $label = 'Flagged advert';

    protected static ?string $pluralLabel = 'Flagged adverts';

    protected static ?string $slug = 'flagged-adverts';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = AnimalListingReview::pendingCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('listing_id')
                    ->label('Advert')
                    ->formatStateUsing(fn (mixed $state): string => ListingDirectory::titleFor((int) $state))
                    ->url(fn (AnimalListingReview $record): ?string => ($listing = ListingDirectory::find((int) $record->getAttribute('listing_id'))) !== null
                        ? route('listings.show', $listing['slug'])
                        : null, shouldOpenInNewTab: true)
                    ->wrap(),
                TextColumn::make('reasons')
                    ->label('Why it was flagged')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->wrap(),
                TextColumn::make('decision')->badge()->colors([
                    'warning' => AnimalListingReview::PENDING,
                    'success' => AnimalListingReview::APPROVED,
                    'danger' => AnimalListingReview::REJECTED,
                ])->sortable(),
                TextColumn::make('user_id')->label('Seller ID')->sortable(),
                TextColumn::make('submitted_at')->since()->sortable(),
                TextColumn::make('note')->label('Note')->toggleable(isToggledHiddenByDefault: true)->wrap(),
            ])
            ->defaultSort('submitted_at', 'asc')
            ->filters([
                SelectFilter::make('decision')
                    ->options([
                        AnimalListingReview::PENDING => 'Pending',
                        AnimalListingReview::APPROVED => 'Approved',
                        AnimalListingReview::REJECTED => 'Rejected',
                    ])
                    ->default(AnimalListingReview::PENDING),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Mark as checked')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('The advert stays live (or goes live if it was waiting) and leaves this list.')
                    ->visible(fn (AnimalListingReview $record): bool => $record->getAttribute('decision') === AnimalListingReview::PENDING)
                    ->action(fn (AnimalListingReview $record): mixed => AnimalReviewDecision::approve($record, (int) auth()->id())),
                Action::make('reject')
                    ->label('Take down')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->schema([
                        Textarea::make('note')
                            ->label('Reason sent to the seller')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->visible(fn (AnimalListingReview $record): bool => $record->getAttribute('decision') === AnimalListingReview::PENDING)
                    ->action(fn (AnimalListingReview $record, array $data): mixed => AnimalReviewDecision::reject($record, (int) auth()->id(), (string) $data['note'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnimalListingReviews::route('/'),
        ];
    }
}
