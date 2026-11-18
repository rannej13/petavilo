<?php

declare(strict_types=1);

namespace Modules\Tv\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Tv\Filament\Admin\Resources\TvVideoResource\Pages;
use Modules\Tv\Models\TvVideo;
use UnitEnum;

class TvVideoResource extends Resource
{
    protected static ?string $model = TvVideo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Petavilo TV';

    protected static ?string $label = 'TV video';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Video')->schema([
                TextInput::make('source_url')->label('YouTube Shorts or TikTok link')->required()->maxLength(300)
                    ->placeholder('https://youtube.com/shorts/... or https://www.tiktok.com/@petavilo/video/...')
                    ->helperText('YouTube plays in privacy mode. TikTok loads only when the viewer presses play.'),
                TextInput::make('title')->required()->maxLength(140),
                Select::make('category')->options(TvVideo::categories())->required(),
                Select::make('species')->options(\Modules\PetTools\Models\Pet::speciesOptions()),
                TextInput::make('credit')->maxLength(120)->placeholder('Petavilo original, or "Shared with permission from @name"'),
                Toggle::make('own_or_licensed')->label('This is our own video, or we have written permission to show it')->required()->accepted(),
            ])->columns(2),
            Section::make('Next step under the video')->description('Link each video to something genuinely related: a safety check, a guide or a tool.')->schema([
                TextInput::make('next_label')->label('Button 1 text')->maxLength(120)->placeholder('Is cucumber safe for cats?'),
                TextInput::make('next_url')->label('Button 1 link')->maxLength(300)->placeholder('/tools/poison-checker?q=cucumber'),
                TextInput::make('next_label_2')->label('Button 2 text')->maxLength(120),
                TextInput::make('next_url_2')->label('Button 2 link')->maxLength(300),
            ])->columns(2),
            Section::make('Showing')->schema([
                Toggle::make('is_published')->label('Show on Petavilo TV')->default(true),
                TextInput::make('sort')->numeric()->default(0)->helperText('Lower numbers show first.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('platform')->badge(),
                TextColumn::make('category')->formatStateUsing(fn (string $state): string => TvVideo::categories()[$state] ?? $state),
                IconColumn::make('is_published')->boolean()->label('Live'),
                TextColumn::make('plays')->sortable(),
                TextColumn::make('sort')->sortable(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('category')->options(TvVideo::categories()),
                SelectFilter::make('platform')->options(['youtube' => 'YouTube', 'tiktok' => 'TikTok']),
            ])
            ->actions(ResourceTableActions::editDelete());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTvVideos::route('/'),
            'create' => Pages\CreateTvVideo::route('/create'),
            'edit' => Pages\EditTvVideo::route('/{record}/edit'),
        ];
    }
}
