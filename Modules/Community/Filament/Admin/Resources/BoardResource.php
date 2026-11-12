<?php

declare(strict_types=1);

namespace Modules\Community\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Community\Filament\Admin\Resources\BoardResource\Pages;
use Modules\Community\Models\Board;
use UnitEnum;

class BoardResource extends Resource
{
    protected static ?string $model = Board::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $label = 'Board';

    protected static ?string $pluralLabel = 'Boards';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(80),
            TextInput::make('slug')->maxLength(80)->helperText('Leave empty to create from the name.'),
            TextInput::make('description')->maxLength(240)->columnSpanFull(),
            Select::make('image')->options(array_combine(array_keys((array) config('catmarket.images', [])), array_keys((array) config('catmarket.images', []))))->helperText('Picture shown on the community page.'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('threads_count')->counts('threads')->label('Discussions'),
                TextColumn::make('sort_order')->sortable(),
                IconColumn::make('is_active')->boolean()->label('Live'),
            ])
            ->defaultSort('sort_order')
            ->actions(ResourceTableActions::editDelete());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoards::route('/'),
            'create' => Pages\CreateBoard::route('/create'),
            'edit' => Pages\EditBoard::route('/{record}/edit'),
        ];
    }
}
