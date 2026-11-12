<?php

declare(strict_types=1);

namespace App\Support;

use Modules\Directory\Models\Business;

final class BusinessDirectory
{
    public static function syncTierForOwner(int $userId, string $tier): void
    {
        Business::syncTierForOwner($userId, $tier);
    }

    public static function ownerIds(): array
    {
        return Business::claimedOwnerIds();
    }
}
