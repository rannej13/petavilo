<?php

declare(strict_types=1);

namespace Modules\Tv\Console;

use Illuminate\Console\Command;
use Modules\Notification\Models\UserNotification;
use Modules\Tv\Models\PetOfWeekEntry;

class PickWinnerCommand extends Command
{
    protected $signature = 'tv:pick-winner';

    protected $description = 'Crown last week\'s Pet of the Week (most votes, earliest entry on a tie).';

    public function handle(): int
    {
        $lastWeek = PetOfWeekEntry::currentWeek()->subWeek();

        if (PetOfWeekEntry::query()->whereDate('week_start', $lastWeek)->where('status', PetOfWeekEntry::STATUS_WINNER)->exists()) {
            $this->info('Already picked.');

            return self::SUCCESS;
        }

        $winner = PetOfWeekEntry::query()
            ->whereDate('week_start', $lastWeek)
            ->where('status', PetOfWeekEntry::STATUS_APPROVED)
            ->where('votes_count', '>', 0)
            ->orderByDesc('votes_count')
            ->orderBy('id')
            ->first();

        if ($winner === null) {
            $this->info('No entries with votes last week.');

            return self::SUCCESS;
        }

        $winner->forceFill(['status' => PetOfWeekEntry::STATUS_WINNER, 'decided_at' => now()])->save();

        try {
            UserNotification::publish(
                (int) $winner->getAttribute('user_id'),
                UserNotification::TYPE_PROMOTION,
                $winner->getAttribute('pet_name').' is Pet of the Week!',
                'Congratulations: '.$winner->getAttribute('votes_count').' people voted for '.$winner->getAttribute('pet_name').'. Their photo is now on Petavilo TV.',
                route('tv.potw'),
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->info('Winner: '.$winner->getAttribute('pet_name'));

        return self::SUCCESS;
    }
}
