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
                TextColumn::make('pilot_ends_at')->label('Free trial ends')->date('j M Y')->sortable()->toggleable(),
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
                Filter::make('trial_invited')->label('Trial offered, not claimed yet')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('pilot_offer_months')->whereNull('owner_user_id')),
                Filter::make('on_pilot')->label('On free trial')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('pilot_tier')->where('pilot_ends_at', '>', now())),
                Filter::make('invited')->label('Invited by email')->query(fn (Builder $query): Builder => $query->whereNotNull('invited_at')),
            ])
            ->actions([
                Action::make('invitePilot')
                    ->label('Email trial invite')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->visible(fn (Business $record): bool => ! $record->isClaimed() && filled($record->getAttribute('email')) && blank($record->getAttribute('unsubscribed_at')))
                    ->form([
                        \Filament\Forms\Components\Select::make('months')->options([1 => '1 month', 2 => '2 months', 3 => '3 months', 6 => '6 months'])->default(3)->required(),
                    ])
                    ->modalHeading(fn (Business $record): string => 'Email '.$record->getAttribute('name').' a free-trial invite')
                    ->modalDescription(fn (Business $record): string => 'Sends one email to '.$record->getAttribute('email').' with a link to claim the listing. Claiming starts free Premium automatically. The email includes an unsubscribe link and your company details.')
                    ->action(function (Business $record, array $data): void {
                        $reason = \Modules\Directory\Support\OutreachPolicy::reasonToSkip($record);

                        if ($reason !== null) {
                            \Filament\Notifications\Notification::make()->title('Not sent: '.$reason)->body('UK rules on marketing emails mean we only email business addresses. Use "Get trial link" and contact them by phone or social media instead.')->warning()->persistent()->send();

                            return;
                        }

                        \Modules\CatMarket\Support\MonetisationConfig::apply();
                        $record->forceFill([
                            'invite_token' => $record->getAttribute('invite_token') ?: \Illuminate\Support\Str::random(40),
                            'pilot_offer_months' => (int) $data['months'],
                        ])->saveQuietly();

                        try {
                            \Illuminate\Support\Facades\Mail::to((string) $record->getAttribute('email'))->send(new \Modules\Directory\Mail\ClaimInvitation($record, false, (int) $data['months']));
                            $record->forceFill(['invited_at' => now(), 'invite_count' => (int) $record->getAttribute('invite_count') + 1])->saveQuietly();
                            \Filament\Notifications\Notification::make()->title('Invite sent to '.$record->getAttribute('email'))->success()->send();
                        } catch (\Throwable $exception) {
                            report($exception);
                            \Filament\Notifications\Notification::make()->title('Sending failed')->body(mb_substr($exception->getMessage(), 0, 300))->danger()->persistent()->send();
                        }
                    }),
                Action::make('pilotLink')
                    ->label('Get trial link')
                    ->icon('heroicon-o-link')
                    ->visible(fn (Business $record): bool => ! $record->isClaimed())
                    ->form([
                        \Filament\Forms\Components\Select::make('months')->options([1 => '1 month', 2 => '2 months', 3 => '3 months', 6 => '6 months'])->default(3)->required(),
                    ])
                    ->modalDescription('Makes a private link for you to send by phone, text, Instagram or Facebook. Whoever opens it can claim this listing, and claiming starts free Premium. Only give it to the business.')
                    ->action(function (Business $record, array $data): void {
                        $record->forceFill([
                            'invite_token' => $record->getAttribute('invite_token') ?: \Illuminate\Support\Str::random(40),
                            'pilot_offer_months' => (int) $data['months'],
                        ])->saveQuietly();

                        \Filament\Notifications\Notification::make()
                            ->title('Trial link for '.$record->getAttribute('name'))
                            ->body(route('directory.claim.token', $record->getAttribute('invite_token')))
                            ->success()->persistent()->send();
                    }),
                Action::make('startPilot')
                    ->label('Free trial')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->visible(fn (Business $record): bool => $record->isClaimed() && ! $record->onPilot())
                    ->form([
                        \Filament\Forms\Components\Select::make('tier')->options(['premium' => 'Premium', 'plus' => 'Plus'])->default('premium')->required(),
                        \Filament\Forms\Components\Select::make('months')->options([1 => '1 month', 2 => '2 months', 3 => '3 months', 6 => '6 months'])->default(3)->required(),
                    ])
                    ->modalDescription('The owner gets the paid features free for this long, with reminders 14 and 3 days before it ends. Then it drops back to whatever they pay for.')
                    ->action(function (Business $record, array $data): void {
                        $record->startPilot((string) $data['tier'], (int) $data['months']);

                        try {
                            \Modules\Notification\Models\UserNotification::publish(
                                (int) $record->getAttribute('owner_user_id'),
                                \Modules\Notification\Models\UserNotification::TYPE_PROMOTION,
                                'Free '.ucfirst((string) $data['tier']).' for '.$record->getAttribute('name'),
                                'You have '.ucfirst((string) $data['tier']).' free until '.$record->getAttribute('pilot_ends_at')->isoFormat('D MMMM YYYY').': more photos, an offer for new clients and top placement. Your dashboard shows views, clicks and calls from Petavilo.',
                                route('directory.panel.index'),
                            );
                        } catch (\Throwable $exception) {
                            report($exception);
                        }
                    }),
                Action::make('endPilot')
                    ->label('End trial')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Business $record): bool => $record->onPilot())
                    ->action(fn (Business $record) => $record->endPilot()),
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
