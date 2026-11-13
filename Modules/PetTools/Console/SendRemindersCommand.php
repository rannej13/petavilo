<?php

declare(strict_types=1);

namespace Modules\PetTools\Console;

use Illuminate\Console\Command;
use Modules\Notification\Models\UserNotification;
use Modules\PetTools\Models\PetRecord;

class SendRemindersCommand extends Command
{
    protected $signature = 'pets:reminders';

    protected $description = 'Notify owners about vaccinations, treatments and other records due in the next 7 days.';

    public function handle(): int
    {
        $count = 0;

        PetRecord::query()->dueWithin(7)->with('pet')->chunkById(200, function ($records) use (&$count): void {
            foreach ($records as $record) {
                $pet = $record->pet;

                if ($pet === null) {
                    continue;
                }

                UserNotification::publish(
                    (int) $pet->getAttribute('user_id'),
                    UserNotification::TYPE_LISTING,
                    $pet->getAttribute('name').': '.$record->getAttribute('title').' due '.$record->getAttribute('due_on')->isoFormat('D MMM'),
                    $record->typeLabel(),
                    route('pettools.passport.index'),
                );
                $record->forceFill(['reminded_at' => now()])->save();
                $count++;
            }
        });

        $this->info("Sent {$count} reminders.");

        return self::SUCCESS;
    }
}
