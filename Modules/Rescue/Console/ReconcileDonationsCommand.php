<?php

declare(strict_types=1);

namespace Modules\Rescue\Console;

use Illuminate\Console\Command;
use Modules\Rescue\Models\Donation;
use Modules\Rescue\Support\DonationGateway;

class ReconcileDonationsCommand extends Command
{
    protected $signature = 'donations:reconcile';

    protected $description = 'Check pending rescue donations with Stripe and mark paid or expired ones.';

    public function handle(): int
    {
        if (! DonationGateway::isConfigured()) {
            return self::SUCCESS;
        }

        Donation::query()
            ->where('status', Donation::STATUS_PENDING)
            ->whereNotNull('stripe_session_id')
            ->where('created_at', '>=', now()->subDays(2))
            ->chunkById(100, static function ($donations): void {
                foreach ($donations as $donation) {
                    DonationGateway::sync($donation);
                }
            });

        return self::SUCCESS;
    }
}
