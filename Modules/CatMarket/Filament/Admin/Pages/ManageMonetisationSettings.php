<?php

declare(strict_types=1);

namespace Modules\CatMarket\Filament\Admin\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Modules\CatMarket\Support\MonetisationConfig;
use Throwable;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CatMarket\Settings\MonetisationSettings;
use UnitEnum;

class ManageMonetisationSettings extends SettingsPage
{
    protected static string $settings = MonetisationSettings::class;

    protected static ?string $title = 'Payments and ads';

    protected static ?string $navigationLabel = 'Payments and ads';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Contact')
                ->description('Shown on the contact, legal and help pages, and used as the reply address on claim invitations.')
                ->schema([
                    TextInput::make('contact_email')->label('Contact email')->email()->placeholder('petavilo@outlook.com'),
                ])->columns(1),
            Section::make('Sending email')
                ->description('Lets the site send sign-up, password reset and message emails. For Resend use host smtp.resend.com, port 587, username resend and your API key as the password.')
                ->schema([
                    TextInput::make('mail_host')->label('SMTP host')->placeholder('smtp.resend.com'),
                    TextInput::make('mail_port')->label('Port')->placeholder('587'),
                    TextInput::make('mail_username')->label('Username')->placeholder('resend'),
                    TextInput::make('mail_password')->label('Password or API key')->password()->revealable()->placeholder('re_...'),
                    TextInput::make('mail_from_address')->label('Send from')->email()->placeholder('hello@petavilo.com'),
                ])->columns(2),
            Section::make('Stripe')
                ->description('Stripe dashboard > Developers > API keys. The webhook signing secret is shown after you add the webhook endpoint https://yourdomain/stripe/webhook.')
                ->schema([
                    TextInput::make('stripe_key')->label('Publishable key')->placeholder('pk_live_...'),
                    TextInput::make('stripe_secret')->label('Secret key')->password()->revealable()->placeholder('sk_live_...'),
                    TextInput::make('stripe_webhook_secret')->label('Webhook signing secret')->password()->revealable()->placeholder('whsec_...'),
                ])->columns(1),
            Section::make('Membership prices')
                ->description('Stripe > Product catalogue. Paste each monthly price ID (price_...).')
                ->schema([
                    TextInput::make('stripe_price_breeder')->label('Breeder (£)'),
                    TextInput::make('stripe_price_breeder_usd')->label('Breeder ($)'),
                    TextInput::make('stripe_price_directory_plus')->label('Directory Plus (£)'),
                    TextInput::make('stripe_price_directory_plus_usd')->label('Directory Plus ($)'),
                    TextInput::make('stripe_price_directory_premium')->label('Directory Premium (£)'),
                    TextInput::make('stripe_price_directory_premium_usd')->label('Directory Premium ($)'),
                    TextInput::make('stripe_price_clinic')->label('Clinic triage widget (£)'),
                    TextInput::make('stripe_price_clinic_usd')->label('Clinic triage widget ($)'),
                ])->columns(2),
            Section::make('Google AdSense')
                ->description('Leave blank until AdSense approves the site.')
                ->schema([
                    TextInput::make('adsense_client')->label('Publisher ID')->placeholder('ca-pub-...'),
                    TextInput::make('adsense_slot_home')->label('Homepage slot ID'),
                    TextInput::make('adsense_slot_listing_infeed')->label('Listing results slot ID'),
                    TextInput::make('adsense_slot_listing_detail')->label('Advert page slot ID'),
                    TextInput::make('adsense_slot_guide_top')->label('Guide top slot ID'),
                    TextInput::make('adsense_slot_guide_bottom')->label('Guide bottom slot ID'),
                    TextInput::make('adsense_slot_directory')->label('Directory slot ID'),
                ])->columns(2),
            Section::make('Partner links for pet tools')
                ->description('Shown on the pain check, dental grader and insurance simulator. Leave blank to hide. Use only your own affiliate or partner links.')
                ->schema([
                    TextInput::make('televet_name_uk')->label('UK video vet name')->placeholder('e.g. FirstVet'),
                    TextInput::make('televet_url_uk')->label('UK video vet link')->url(),
                    TextInput::make('televet_name_us')->label('US video vet name'),
                    TextInput::make('televet_url_us')->label('US video vet link')->url(),
                    \Filament\Forms\Components\Textarea::make('insurers_uk')->label('UK insurer links')->rows(3)->helperText('One per line: Name | https://link'),
                    \Filament\Forms\Components\Textarea::make('insurers_us')->label('US insurer links')->rows(3)->helperText('One per line: Name | https://link'),
                ])->columns(2),
            Section::make('Amazon Associates')
                ->description('Your tracking IDs. Amazon links in guides get the right tag automatically.')
                ->schema([
                    TextInput::make('amazon_tag_uk')->label('UK tag (amazon.co.uk)')->placeholder('petavilo-21'),
                    TextInput::make('amazon_tag_us')->label('US tag (amazon.com)')->placeholder('petavilo-20'),
                ])->columns(2),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->action(function (): void {
                    MonetisationConfig::apply();
                    $to = (string) auth()->user()?->email;

                    try {
                        Mail::raw('This is a test email from Petavilo. If you can read this, sending works.', function ($message) use ($to): void {
                            $message->to($to)->subject('Petavilo test email');
                        });
                        Notification::make()->title('Test email sent to '.$to)->success()->send();
                    } catch (Throwable $exception) {
                        Notification::make()->title('Sending failed')->body(mb_substr($exception->getMessage(), 0, 300))->danger()->send();
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        $email = trim((string) ($this->data['contact_email'] ?? ''));

        if ($email !== '') {
            \Modules\CatMarket\Support\ContactEmail::sync($email);
        }
    }
}
