<?php

declare(strict_types=1);

namespace Modules\Directory\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Directory\Models\Business;

class ImportPlacesCommand extends Command
{
    private const FILE = 'places/places.csv';

    private const MARKER = 'places/imported-at';

    protected $signature = 'directory:import-places {--force : Import even if the file has not changed}';

    protected $description = 'Import vets, groomers, boarding, rescues and pet shops from the Overture Places export.';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $path = storage_path('app/'.self::FILE);

        if (! is_file($path)) {
            $this->info('No places file yet.');

            return self::SUCCESS;
        }

        $fileTime = (string) filemtime($path);

        if (! $this->option('force') && $disk->exists(self::MARKER) && trim((string) $disk->get(self::MARKER)) === $fileTime) {
            $this->info('Places already imported.');

            return self::SUCCESS;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return self::FAILURE;
        }

        $header = fgetcsv($handle) ?: [];
        $types = array_keys(Business::types());
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $record = array_combine($header, array_slice(array_pad($row, count($header), ''), 0, count($header)));

            if (! is_array($record) || ! in_array($record['type'] ?? '', $types, true) || blank($record['name'] ?? null)) {
                continue;
            }

            $sourceId = 'overture:'.Str::limit((string) $record['source_id'], 54, '');
            $existing = Business::withTrashed()->where('source_id', $sourceId)->first();

            if ($existing instanceof Business && ($existing->trashed() || $existing->isClaimed())) {
                continue;
            }

            $country = strtoupper((string) $record['country']) === 'US' ? 'US' : 'GB';
            $town = trim((string) $record['town']);

            if ($country === 'US' && $town !== '' && filled($record['region'])) {
                $town .= ', '.strtoupper(Str::afterLast((string) $record['region'], '-'));
            }

            $attributes = [
                'name' => Str::limit(trim((string) $record['name']), 120, ''),
                'type' => (string) $record['type'],
                'country' => $country,
                'town' => Str::limit($town, 80, '') ?: null,
                'postcode' => Str::limit(trim((string) $record['postcode']), 12, '') ?: null,
                'address_line' => Str::limit(trim((string) $record['address']), 160, '') ?: null,
                'phone' => Str::limit(trim((string) $record['phone']), 40, '') ?: null,
                'website' => Str::limit(trim((string) $record['website']), 200, '') ?: null,
                'social_url' => Str::limit(trim((string) ($record['social'] ?? '')), 200, '') ?: null,
                'latitude' => is_numeric($record['latitude'] ?? null) ? round((float) $record['latitude'], 7) : null,
                'longitude' => is_numeric($record['longitude'] ?? null) ? round((float) $record['longitude'], 7) : null,
                'is_published' => true,
            ];

            $email = strtolower(trim((string) ($record['email'] ?? '')));
            $email = filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 160 ? $email : null;

            $key = Business::dedupeKeyFor($country, $attributes['name'], $attributes['postcode'], $attributes['town']);

            if ($key !== null && Business::query()
                ->where('dedupe_key', $key)
                ->when($existing instanceof Business, static fn ($query) => $query->whereKeyNot($existing->getKey()))
                ->exists()) {
                $skipped++;

                continue;
            }

            if ($existing instanceof Business) {
                if ($email !== null && blank($existing->getAttribute('email'))) {
                    $attributes['email'] = $email;
                }

                $existing->fill($attributes)->save();
                $updated++;

                continue;
            }

            $business = new Business($attributes + ['email' => $email]);
            $business->forceFill(['source' => 'overture', 'source_id' => $sourceId, 'tier' => 'basic']);
            $business->save();
            $created++;
        }

        fclose($handle);
        $disk->put(self::MARKER, $fileTime);
        $this->info("Places imported: {$created} new, {$updated} updated, {$skipped} duplicates skipped.");
        $this->call('directory:dedupe');

        return self::SUCCESS;
    }
}
