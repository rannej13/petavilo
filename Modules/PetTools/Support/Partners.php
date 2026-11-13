<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

use Modules\CatMarket\Settings\MonetisationSettings;
use Throwable;

final class Partners
{
    public static function televet(string $country): ?array
    {
        $suffix = $country === 'US' ? 'us' : 'uk';
        $url = self::value('televet_url_'.$suffix);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return ['name' => self::value('televet_name_'.$suffix) ?: 'Video vet', 'url' => $url];
    }

    public static function insurers(string $country): array
    {
        $lines = preg_split('/\R/', self::value($country === 'US' ? 'insurers_us' : 'insurers_uk')) ?: [];

        return collect($lines)
            ->map(static fn (string $line): array => array_map('trim', explode('|', $line, 2)))
            ->filter(static fn (array $parts): bool => count($parts) === 2 && $parts[0] !== '' && filter_var($parts[1], FILTER_VALIDATE_URL) !== false)
            ->map(static fn (array $parts): array => ['name' => $parts[0], 'url' => $parts[1]])
            ->values()
            ->all();
    }

    private static function value(string $key): string
    {
        try {
            return trim((string) app(MonetisationSettings::class)->{$key});
        } catch (Throwable) {
            return '';
        }
    }
}
