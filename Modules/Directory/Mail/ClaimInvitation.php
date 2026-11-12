<?php

declare(strict_types=1);

namespace Modules\Directory\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Modules\Directory\Models\Business;

class ClaimInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Business $business, public bool $isFollowUp = false) {}

    public function envelope(): Envelope
    {
        $site = (string) config('catmarket.site_name', 'Petavilo');
        $name = (string) $this->business->getAttribute('name');
        $from = (string) (config('directory.outreach.from_address') ?: config('mail.from.address'));

        return new Envelope(
            from: new Address($from, (string) config('directory.outreach.from_name', $site)),
            replyTo: [new Address($from, (string) config('directory.outreach.from_name', $site))],
            subject: $this->isFollowUp
                ? "Reminder: {$name} on {$site}"
                : "{$name} is listed on {$site}. Claim it free",
        );
    }

    public function headers(): Headers
    {
        $url = route('directory.unsubscribe', $this->business->getAttribute('invite_token'));
        $from = (string) (config('directory.outreach.from_address') ?: config('mail.from.address'));

        return new Headers(text: [
            'List-Unsubscribe' => '<'.$url.'>, <mailto:'.$from.'?subject=unsubscribe>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        $business = $this->business;
        $token = (string) $business->getAttribute('invite_token');
        $company = (array) config('catmarket.company', []);

        return new Content(
            view: 'directory::mail.claim-invitation',
            text: 'directory::mail.claim-invitation-text',
            with: [
                'site' => (string) config('catmarket.site_name', 'Petavilo'),
                'business' => $business,
                'typeName' => strtolower(Business::typeName((string) $business->getAttribute('type'), true)),
                'listingUrl' => route('directory.show', [$business->getAttribute('type'), $business->getAttribute('slug')]),
                'claimUrl' => route('directory.claim.token', $token),
                'unsubscribeUrl' => route('directory.unsubscribe', $token),
                'isFollowUp' => $this->isFollowUp,
                'senderName' => (string) config('directory.outreach.sender_name', ''),
                'companyLine' => collect([
                    $company['name'] ?? null,
                    filled($company['number'] ?? null) ? 'Company number '.$company['number'] : null,
                    filled($company['address'] ?? null) ? 'Registered office: '.$company['address'] : null,
                ])->filter()->implode(' · '),
            ],
        );
    }
}
