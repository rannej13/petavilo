<?php

declare(strict_types=1);

namespace Modules\CatMarket\Console;

use Illuminate\Console\Command;
use Modules\CatMarket\Support\SiteEmails;
use Modules\Listing\Models\Listing;

class ListingRemindersCommand extends Command
{
    protected $signature = 'catmarket:listing-reminders';

    protected $description = 'Warn sellers whose adverts end in about three days.';

    public function handle(): int
    {
        $sent = 0;

        Listing::query()
            ->active()
            ->where('is_example', false)
            ->whereNotNull('user_id')
            ->whereBetween('expires_at', [now()->addDays(2), now()->addDays(3)])
            ->chunkById(200, static function ($listings) use (&$sent): void {
                foreach ($listings as $listing) {
                    SiteEmails::listingExpiringSoon($listing);
                    $sent++;
                }
            });

        $this->info("Sent {$sent} reminders.");

        return self::SUCCESS;
    }
}
