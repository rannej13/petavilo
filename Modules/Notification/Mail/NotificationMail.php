<?php

declare(strict_types=1);

namespace Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Notification\Models\UserNotification;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public UserNotification $notification)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: (string) $this->notification->getAttribute('title'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'notification::mail.notification',
            text: 'notification::mail.notification-text',
            with: [
                'title' => (string) $this->notification->getAttribute('title'),
                'body' => (string) ($this->notification->getAttribute('body') ?? ''),
                'url' => (string) ($this->notification->getAttribute('action_url') ?: route('panel.notifications.index')),
                'settingsUrl' => route('panel.profile.edit'),
                'site' => (string) config('catmarket.site_name', config('app.name')),
            ],
        );
    }
}
