<?php

declare(strict_types=1);

namespace Modules\Directory\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Directory\Models\Business;
use Modules\Directory\Models\DirectorySuppression;

final class OutreachPolicy
{
    private const FREE_MAIL = [
        'gmail.com', 'googlemail.com', 'hotmail.com', 'hotmail.co.uk', 'outlook.com', 'live.com', 'live.co.uk',
        'yahoo.com', 'yahoo.co.uk', 'icloud.com', 'me.com', 'aol.com', 'btinternet.com', 'sky.com', 'virginmedia.com',
        'talktalk.net', 'msn.com', 'mail.com', 'gmx.com', 'protonmail.com', 'proton.me', 'ymail.com',
    ];

    public static function candidates(): Builder
    {
        $config = (array) config('directory.outreach', []);
        $followUpBefore = now()->subDays((int) ($config['follow_up_after_days'] ?? 14));

        return Business::query()
            ->published()
            ->whereNull('owner_user_id')
            ->whereNotNull('email')
            ->whereNull('unsubscribed_at')
            ->whereIn('country', (array) ($config['countries'] ?? ['US']))
            ->where('invite_count', '<', (int) ($config['max_emails'] ?? 2))
            ->where(static fn (Builder $query): Builder => $query
                ->whereNull('invited_at')
                ->orWhere('invited_at', '<=', $followUpBefore))
            ->orderBy('invite_count')
            ->orderByRaw("CASE WHEN type = 'vets' THEN 0 ELSE 1 END")
            ->orderBy('id');
    }

    public static function reasonToSkip(Business $business): ?string
    {
        $email = strtolower(trim((string) $business->getAttribute('email')));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return 'invalid email';
        }

        if (DirectorySuppression::contains($email)) {
            return 'unsubscribed';
        }

        if ((string) $business->getAttribute('country') === 'GB') {
            if (! in_array((string) $business->getAttribute('type'), (array) config('directory.outreach.uk_types', []), true)) {
                return 'UK business type likely to be a sole trader';
            }

            $domain = Str::after($email, '@');

            if (in_array($domain, self::FREE_MAIL, true)) {
                return 'UK personal mailbox';
            }

            if (! in_array(Str::before($email, '@'), (array) config('directory.outreach.role_prefixes', []), true)) {
                return 'UK named-person address';
            }
        }

        return null;
    }
}
