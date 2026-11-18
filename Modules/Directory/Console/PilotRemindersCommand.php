<?php

declare(strict_types=1);

namespace Modules\Directory\Console;

use Illuminate\Console\Command;
use Modules\Directory\Models\Business;
use Modules\Notification\Models\UserNotification;

class PilotRemindersCommand extends Command
{
    protected $signature = 'directory:pilots';

    protected $description = 'Remind pilot businesses before their free trial ends, and end trials that have run out.';

    public function handle(): int
    {
        $reminded = 0;
        $ended = 0;

        Business::query()
            ->whereNotNull('pilot_tier')
            ->whereNotNull('owner_user_id')
            ->get()
            ->each(function (Business $business) use (&$reminded, &$ended): void {
                $ends = $business->getAttribute('pilot_ends_at');

                if ($ends === null) {
                    return;
                }

                if ($ends->isPast()) {
                    $business->endPilot();
                    $this->tell($business, 'Your free '.ucfirst((string) $business->getAttribute('tier')).' trial has ended', $this->statsLine($business).' Your listing is back to the free Basic profile. Choose a plan to keep your photos, offer and top placement.');
                    $ended++;

                    return;
                }

                $daysLeft = (int) now()->startOfDay()->diffInDays($ends->copy()->startOfDay());
                $last = $business->getAttribute('pilot_reminded_at');

                if (in_array($daysLeft, [14, 3], true) && ($last === null || $last->lt(now()->subDays(2)))) {
                    $this->tell($business, 'Your free '.ucfirst((string) $business->getAttribute('pilot_tier')).' trial ends in '.$daysLeft.' days', $this->statsLine($business).' Choose a plan before '.$ends->isoFormat('D MMMM').' to keep everything as it is.');
                    $business->forceFill(['pilot_reminded_at' => now()])->saveQuietly();
                    $reminded++;
                }
            });

        $this->info("Reminded {$reminded}, ended {$ended}.");

        return self::SUCCESS;
    }

    private function statsLine(Business $business): string
    {
        $stats = $business->statsForDays(30);

        return sprintf(
            'In the last 30 days, %s was viewed %d times, with %d website visits, %d booking clicks and %d phone taps from Petavilo.',
            $business->getAttribute('name'),
            $stats['views'],
            $stats['website'],
            $stats['booking'],
            $stats['phone'],
        );
    }

    private function tell(Business $business, string $title, string $body): void
    {
        try {
            UserNotification::publish((int) $business->getAttribute('owner_user_id'), UserNotification::TYPE_PROMOTION, $title, $body, route('billing.membership.index'));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
