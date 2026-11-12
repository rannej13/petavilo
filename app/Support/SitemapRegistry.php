<?php

declare(strict_types=1);

namespace App\Support;

use Closure;

final class SitemapRegistry
{
    private static array $sources = [];

    public static function register(Closure $source): void
    {
        self::$sources[] = $source;
    }

    public static function entries(): array
    {
        $entries = [];

        foreach (self::$sources as $source) {
            foreach ((array) $source() as $entry) {
                if (is_array($entry) && isset($entry['url'])) {
                    $entries[] = ['url' => (string) $entry['url'], 'updated' => $entry['updated'] ?? null];
                }
            }
        }

        return $entries;
    }
}
