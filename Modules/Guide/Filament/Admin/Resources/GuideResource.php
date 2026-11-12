<?php

declare(strict_types=1);

namespace Modules\Guide\Filament\Admin\Resources;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Admin\Support\Filament\ResourceTableActions;
use Modules\Guide\Filament\Admin\Resources\GuideResource\Pages;
use Modules\Guide\Models\Guide;
use UnitEnum;

class GuideResource extends Resource
{
    protected static ?string $model = Guide::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $label = 'Guide';

    protected static ?string $pluralLabel = 'Guides';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')->required()->maxLength(160)->live(onBlur: true)
                ->afterStateUpdated(static function (Set $set, ?string $state, ?Guide $record): void {
                    if ($record === null) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')->required()->maxLength(160)->unique(ignoreRecord: true),
            Select::make('type')->options(Guide::TYPES)->required(),
            Textarea::make('excerpt')->rows(2)->maxLength(320),
            FileUpload::make('cover_image')->image()->disk('public')->directory('guides')->visibility('public')->maxSize(6144)
                ->helperText('Optional. Without one, a picture matching the guide type is used.'),
            MarkdownEditor::make('body')
                ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('guides')->fileAttachmentsVisibility('public')
                ->columnSpanFull()
                ->helperText('Use ## for section headings. Tables are supported. External links are marked sponsored automatically.'),
            Toggle::make('has_affiliate_links')->helperText('Shows the affiliate disclosure at the top. Turned on automatically for Amazon links.'),
            TextInput::make('sponsor_name')->helperText('Fill only for paid sponsored content. Shows a "Sponsored by" label.'),
            TextInput::make('author_name'),
            DatePicker::make('reviewed_on'),
            TextInput::make('meta_title')->maxLength(70),
            Textarea::make('meta_description')->rows(2)->maxLength(160),
            Toggle::make('is_published'),
            DateTimePicker::make('published_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->wrap(),
                TextColumn::make('type')->formatStateUsing(fn (string $state): string => Guide::TYPES[$state] ?? $state)->badge(),
                IconColumn::make('has_affiliate_links')->boolean()->label('Affiliate'),
                IconColumn::make('is_published')->boolean()->label('Live'),
                TextColumn::make('published_at')->date()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('type')->options(Guide::TYPES),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->actions(ResourceTableActions::editDelete());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuides::route('/'),
            'create' => Pages\CreateGuide::route('/create'),
            'edit' => Pages\EditGuide::route('/{record}/edit'),
        ];
    }
}
