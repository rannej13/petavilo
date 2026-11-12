<?php

declare(strict_types=1);

namespace Modules\Directory\Filament\Admin\Resources\BusinessResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Modules\Directory\Filament\Admin\Resources\BusinessResource;
use Modules\Directory\Models\Business;
use Throwable;

class ListBusinesses extends ListRecords
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        $ready = filled(config('mail.mailers.outreach.username')) && filled(config('mail.mailers.outreach.password'));

        return [
            ActionGroup::make([
                Action::make('outreachStatus')
                    ->label('Status')
                    ->icon('heroicon-o-information-circle')
                    ->modalHeading('Claim invitations')
                    ->modalDescription(fn (): string => sprintf(
                        '%s unclaimed businesses have an email address. %s invited so far, %s claimed. %s Daily limit: %d.',
                        number_format(Business::query()->whereNull('owner_user_id')->whereNotNull('email')->count()),
                        number_format(Business::query()->whereNotNull('invited_at')->count()),
                        number_format(Business::query()->whereNotNull('owner_user_id')->count()),
                        $ready
                            ? ((bool) config('directory.outreach.enabled') ? 'Sending automatically on weekdays.' : 'Automatic sending is off (OUTREACH_ENABLED=false).')
                            : 'No sending mailbox connected, so use "Download invite list" and send from your own mailbox.',
                        (int) config('directory.outreach.daily_limit', 30),
                    ))
                    ->modalSubmitAction(false),
                Action::make('outreachExport')
                    ->label('Download invite list to send from Outlook')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->modalDescription(fn (): string => 'Downloads up to '.(int) config('directory.outreach.daily_limit', 30).' businesses with their email, a subject and a ready-to-paste message containing their personal claim link. They are marked as invited, so tomorrow\'s list is different. Send them from your own mailbox, a few at a time.')
                    ->requiresConfirmation()
                    ->action(fn () => redirect()->route('directory.admin.invites')),
                Action::make('outreachTest')
                    ->label('Send me a test email')
                    ->icon('heroicon-o-envelope')
                    ->visible($ready)
                    ->requiresConfirmation()
                    ->modalDescription(fn (): string => 'Sends one example invitation to '.auth()->user()?->email.'. No business is emailed.')
                    ->action(function (): void {
                        try {
                            Artisan::call('directory:invite', ['--to' => (string) auth()->user()?->email]);
                            Notification::make()->title(trim(Artisan::output()) ?: 'Sent')->success()->send();
                        } catch (Throwable $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('outreachSend')
                    ->label('Send today\'s batch now')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible($ready)
                    ->requiresConfirmation()
                    ->modalDescription(fn (): string => 'Emails up to '.(int) config('directory.outreach.daily_limit', 30).' unclaimed businesses in the background. Each business gets one invitation and at most one reminder.')
                    ->action(function (): void {
                        Artisan::queue('directory:invite');
                        Notification::make()->title('Sending in the background')->success()->send();
                    }),
            ])->label('Claim invitations')->icon('heroicon-o-megaphone')->button(),
            CreateAction::make(),
        ];
    }
}
