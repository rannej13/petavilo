<?php

declare(strict_types=1);

namespace Modules\Directory\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Directory\Models\Business;

class DedupeBusinessesCommand extends Command
{
    protected $signature = 'directory:dedupe {--dry-run : Only report what would be removed}';

    protected $description = 'Hide duplicate businesses (same name at the same postcode or town), keeping the claimed or most complete one.';

    public function handle(): int
    {
        $this->backfillKeys();
        $this->hideNonCatVets();

        $removed = 0;

        Business::query()
            ->select('dedupe_key')
            ->whereNotNull('dedupe_key')
            ->groupBy('dedupe_key')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('dedupe_key')
            ->chunk(500, function (Collection $groups) use (&$removed): void {
                foreach ($groups as $group) {
                    $removed += $this->resolve((string) $group->getAttribute('dedupe_key'));
                }
            });

        $this->info(($this->option('dry-run') ? 'Would hide ' : 'Hidden ').$removed.' duplicate businesses.');

        return self::SUCCESS;
    }

    private function backfillKeys(): void
    {
        Business::query()
            ->whereNull('dedupe_key')
            ->select(['id', 'country', 'name', 'postcode', 'town'])
            ->chunkById(2000, static function (Collection $rows): void {
                foreach ($rows as $row) {
                    $key = Business::dedupeKeyFor((string) $row->country, (string) $row->name, $row->postcode, $row->town);

                    if ($key !== null) {
                        DB::table('businesses')->where('id', $row->id)->update(['dedupe_key' => $key]);
                    }
                }
            });
    }

    private function hideNonCatVets(): void
    {
        if ($this->option('dry-run')) {
            return;
        }

        $hidden = Business::query()
            ->whereNull('owner_user_id')
            ->whereNotNull('source')
            ->whereRaw("name ~* '\\m(equine|equestrian|horse|farm|livestock|large animal|cattle|poultry|bovine)\\M'")
            ->update(['is_published' => false]);

        if ($hidden > 0) {
            $this->info("Hid {$hidden} horse and farm businesses.");
        }

        Business::query()
            ->whereNotNull('source')
            ->where('country', 'GB')
            ->whereRaw('town = UPPER(town)')
            ->whereRaw("town ~ '[A-Z]'")
            ->update(['town' => DB::raw('INITCAP(town)')]);
    }

    private function resolve(string $key): int
    {
        $rows = Business::query()->where('dedupe_key', $key)->get();

        $keep = $rows->sortByDesc(static fn (Business $business): array => [
            $business->isClaimed() ? 1 : 0,
            blank($business->getAttribute('source')) ? 1 : 0,
            self::filledCount($business),
            -1 * (int) $business->getKey(),
        ])->first();

        $removed = 0;

        foreach ($rows as $business) {
            if ($business->is($keep) || $business->isClaimed()) {
                continue;
            }

            if (! $this->option('dry-run')) {
                foreach (['phone', 'email', 'website', 'address_line', 'postcode', 'latitude', 'longitude', 'social_url'] as $field) {
                    if (blank($keep->getAttribute($field)) && filled($business->getAttribute($field))) {
                        $keep->setAttribute($field, $business->getAttribute($field));
                    }
                }

                $business->delete();
            }

            $removed++;
        }

        if (! $this->option('dry-run') && $keep->isDirty()) {
            $keep->saveQuietly();
        }

        return $removed;
    }

    private static function filledCount(Business $business): int
    {
        return collect(['phone', 'email', 'website', 'address_line', 'postcode', 'description', 'opening_hours', 'latitude'])
            ->filter(static fn (string $field): bool => filled($business->getAttribute($field)))
            ->count();
    }
}
