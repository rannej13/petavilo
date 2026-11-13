<?php

declare(strict_types=1);

namespace Modules\CatMarket\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SiteMail extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $heading,
        public array $lines,
        public ?string $buttonLabel = null,
        public ?string $buttonUrl = null,
        public ?string $footer = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->heading);
    }

    public function content(): Content
    {
        return new Content(
            view: 'catmarket::mail.site',
            text: 'catmarket::mail.site-text',
            with: ['site' => (string) config('catmarket.site_name', config('app.name', 'Petavilo'))],
        );
    }
}
