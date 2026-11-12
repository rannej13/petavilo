<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Community\Filament\Admin\Resources\ThreadResource\Pages;
use Modules\Community\Models\Thread;
use UnitEnum;

class ThreadResource extends Resource
{
    protected static ?string $model = Thread::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $label = 'Discussion';

    protected static ?string $pluralLabel = 'Discussions';

    public static function getNavigationBadge(): ?string
    {
        $count = Thread::query()->where('report_count', '>', 0)->where('is_hidden', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('board_id')->relationship('board', 'name')->required(),
            TextInput::make('title')->required()->maxLength(160),
            Textarea::make('body')->required()->rows(10)->columnSpanFull(),
            Toggle::make('is_pinned'),
            Toggle::make('is_locked')->label('Closed to replies'),
            Toggle::make('is_hidden')->label('Hidden from members'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->wrap()->url(fn (Thread $record): string => $record->url(), true),
                TextColumn::make('board.name')->badge(),
                TextColumn::make('user.name')->label('By'),
                TextColumn::make('reply_count')->label('Replies')->sortable(),
                TextColumn::make('report_count')->label('Reports')->sortable()->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray'),
                IconColumn::make('is_pinned')->boolean()->label('Pinned'),
                IconColumn::make('is_hidden')->boolean()->label('Hidden'),
                TextColumn::make('last_post_at')->since()->sortable(),
            ])
            ->defaultSort('last_post_at', 'desc')
            ->filters([
                Filter::make('reported')->query(fn (Builder $query): Builder => $query->where('report_count', '>', 0)),
                SelectFilter::make('board')->relationship('board', 'name'),
                TernaryFilter::make('is_hidden')->label('Hidden'),
            ])
            ->actions([
                Action::make('toggleHidden')
                    ->label(fn (Thread $record): string => $record->getAttribute('is_hidden') ? 'Show' : 'Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(fn (Thread $record): bool => $record->forceFill(['is_hidden' => ! $record->getAttribute('is_hidden'), 'report_count' => 0])->save()),
                Action::make('clearReports')
                    ->label('Keep')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Thread $record): bool => (int) $record->getAttribute('report_count') > 0)
                    ->action(fn (Thread $record): bool => $record->forceFill(['report_count' => 0])->save()),
                ...ResourceTableActions::editDelete(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThreads::route('/'),
            'create' => Pages\CreateThread::route('/create'),
            'edit' => Pages\EditThread::route('/{record}/edit'),
        ];
    }
}
