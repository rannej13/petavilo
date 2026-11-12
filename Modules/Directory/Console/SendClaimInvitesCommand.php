<?php

declare(strict_types=1);

namespace Modules\Directory\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Directory\Mail\ClaimInvitation;
use Modules\Directory\Models\Business;
use Modules\Directory\Support\OutreachPolicy;
use Throwable;

class SendClaimInvitesCommand extends Command
{
    protected $signature = 'directory:invite {--limit= : Maximum emails to send now} {--dry-run : List who would be emailed without sending} {--to= : Send one test email to this address using the first eligible business}';

    protected $description = 'Email unclaimed businesses a one-off invitation to claim their free listing (with one reminder).';

    public function handle(): int
    {
        $limit = max(1, (int) ($this->option('limit') ?: config('directory.outreach.daily_limit', 30)));
        $dryRun = (bool) $this->option('dry-run');
        $testTo = trim((string) $this->option('to'));

        if (! $dryRun && (blank(config('mail.mailers.outreach.username')) || blank(config('mail.mailers.outreach.password')))) {
            $this->error('No sending mailbox is connected (OUTREACH_MAIL_* in .env). Use the invite list download instead.');

            return self::FAILURE;
        }

        $sent = 0;
        $skipped = [];

        foreach (OutreachPolicy::candidates()->cursor() as $business) {
            if ($sent >= $limit) {
                break;
            }

            $reason = OutreachPolicy::reasonToSkip($business);

            if ($reason !== null) {
                $skipped[$reason] = ($skipped[$reason] ?? 0) + 1;

                if (! $dryRun && $testTo === '') {
                    $business->forceFill(['invite_count' => 99])->saveQuietly();
                }

                continue;
            }

            if (blank($business->getAttribute('invite_token'))) {
                $business->forceFill(['invite_token' => Str::random(40)])->saveQuietly();
            }

            $isFollowUp = (int) $business->getAttribute('invite_count') > 0;

            if ($dryRun) {
                $this->line(($isFollowUp ? '[reminder] ' : '').$business->getAttribute('name').' <'.$business->getAttribute('email').'> '.$business->getAttribute('country'));
                $sent++;

                continue;
            }

            try {
                Mail::mailer((string) config('directory.outreach.mailer', 'outreach'))
                    ->to($testTo !== '' ? $testTo : (string) $business->getAttribute('email'))
                    ->send(new ClaimInvitation($business, $isFollowUp));
            } catch (Throwable $exception) {
                $this->warn($business->getAttribute('name').': '.$exception->getMessage());

                continue;
            }

            if ($testTo !== '') {
                $this->info('Test email sent to '.$testTo.' for '.$business->getAttribute('name'));

                return self::SUCCESS;
            }

            $business->forceFill([
                'invited_at' => now(),
                'invite_count' => (int) $business->getAttribute('invite_count') + 1,
            ])->saveQuietly();

            $sent++;
            usleep(random_int(2_000_000, 6_000_000));
        }

        foreach ($skipped as $reason => $count) {
            $this->line("Skipped {$count}: {$reason}");
        }

        $this->info(($dryRun ? 'Would send ' : 'Sent ').$sent.' invitations.');

        return self::SUCCESS;
    }
}
