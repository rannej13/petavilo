<?php

declare(strict_types=1);

namespace Modules\Ads\Support;

final class AdPlacement
{
    public static function client(): ?string
    {
        $client = trim((string) config('ads.client', ''));

        return $client !== '' ? $client : null;
    }

    public static function enabledHere(): bool
    {
        if (self::client() === null) {
            return false;
        }

        $route = request()->route();

        if ($route === null) {
            return true;
        }

        return ! request()->routeIs(...(array) config('ads.excluded_routes', []));
    }

    public static function slotId(string $name): ?string
    {
        $slot = trim((string) (config('ads.slots')[$name] ?? ''));

        return $slot !== '' ? $slot : null;
    }

    public static function shows(string $name): bool
    {
        return self::enabledHere() && self::slotId($name) !== null;
    }

    public static function infeedAfter(int $position): bool
    {
        $every = max(0, (int) config('ads.infeed_every', 0));

        return $every > 0 && $position > 0 && $position % $every === 0;
    }

    public static function adsTxt(): ?string
    {
        $client = self::client();

        if ($client === null) {
            return null;
        }

        $publisher = preg_replace('/^ca-/', '', $client);

        return 'google.com, '.$publisher.', DIRECT, f08c47fec0942fa0';
    }
}
