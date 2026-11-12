<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Community\Filament\Admin\Resources\ReplyResource\Pages;
use Modules\Community\Models\Reply;
use UnitEnum;

class ReplyResource extends Resource
{
    protected static ?string $model = Reply::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $label = 'Reply';

    protected static ?string $pluralLabel = 'Replies';

    public static function getNavigationBadge(): ?string
    {
        $count = Reply::query()->where('report_count', '>', 0)->where('is_hidden', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Textarea::make('body')->required()->rows(8)->columnSpanFull(),
            Toggle::make('is_hidden')->label('Hidden from members'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('body')->limit(120)->wrap()->searchable(),
                TextColumn::make('thread.title')->label('Discussion')->limit(50)->url(fn (Reply $record): ?string => $record->thread?->url(), true),
                TextColumn::make('user.name')->label('By'),
                TextColumn::make('report_count')->label('Reports')->sortable()->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray'),
                IconColumn::make('is_hidden')->boolean()->label('Hidden'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('reported')->query(fn (Builder $query): Builder => $query->where('report_count', '>', 0)),
                TernaryFilter::make('is_hidden')->label('Hidden'),
            ])
            ->actions([
                Action::make('toggleHidden')
                    ->label(fn (Reply $record): string => $record->getAttribute('is_hidden') ? 'Show' : 'Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(fn (Reply $record): bool => $record->forceFill(['is_hidden' => ! $record->getAttribute('is_hidden'), 'report_count' => 0])->save()),
                Action::make('clearReports')
                    ->label('Keep')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Reply $record): bool => (int) $record->getAttribute('report_count') > 0)
                    ->action(fn (Reply $record): bool => $record->forceFill(['report_count' => 0])->save()),
                ...ResourceTableActions::editDelete(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReplies::route('/'),
            'edit' => Pages\EditReply::route('/{record}/edit'),
        ];
    }
}
