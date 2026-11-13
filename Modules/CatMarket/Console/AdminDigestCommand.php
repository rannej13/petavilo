<?php

declare(strict_types=1);

namespace Modules\CatMarket\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\CatMarket\Models\AnimalListingReview;
use Modules\CatMarket\Support\SiteEmails;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;
use Throwable;

class AdminDigestCommand extends Command
{
    protected $signature = 'catmarket:admin-digest';

    protected $description = 'Email the site admin a summary of the last 24 hours.';

    public function handle(): int
    {
        $since = now()->subDay();
        $rows = [
            'New members' => User::query()->where('created_at', '>=', $since)->where('email', 'not like', '%.invalid')->count(),
            'New adverts' => Listing::query()->where('created_at', '>=', $since)->where('is_example', false)->count(),
            'Adverts waiting for your check' => AnimalListingReview::pendingCount(),
        ];

        $rows += $this->optional('Rescues waiting to be verified', 'rescue_organisations', static fn ($q) => $q->where('status', 'pending'));
        $rows += $this->optional('Adoption applications', 'adoption_applications', static fn ($q) => $q->where('created_at', '>=', $since));
        $rows += $this->optional('Donations', 'rescue_donations', static fn ($q) => $q->where('status', 'paid')->where('paid_at', '>=', $since));
        $rows += $this->optional('Vet check requests', 'vet_leads', static fn ($q) => $q->where('created_at', '>=', $since));
        $rows += $this->optional('Reports from members', 'reports', static fn ($q) => $q->where('created_at', '>=', $since));

        if (array_sum($rows) === 0) {
            $this->info('Nothing to report.');

            return self::SUCCESS;
        }

        $lines = ['Here is what happened in the last 24 hours.', ''];

        foreach ($rows as $label => $count) {
            $lines[] = $label.': '.$count;
        }

        SiteEmails::admin('Daily summary for '.now()->isoFormat('D MMMM'), $lines, url('/admin'));
        $this->info('Summary sent.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function optional(string $label, string $table, callable $scope): array
    {
        try {
            if (! Schema::hasTable($table)) {
                return [];
            }

            return [$label => (int) $scope(\Illuminate\Support\Facades\DB::table($table))->count()];
        } catch (Throwable) {
            return [];
        }
    }
}
