<?php

declare(strict_types=1);

namespace App\Support;

use Modules\Billing\Support\MembershipCatalog;
use Modules\User\App\Models\User;

final class MembershipDirectory
{
    public static function isBreederMember(?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        $user = User::query()->find($userId);

        return $user instanceof User && MembershipCatalog::isBreeder($user);
    }

    public static function directoryTierFor(int $userId): string
    {
        $user = User::query()->find($userId);

        return $user instanceof User ? MembershipCatalog::directoryTier($user) : 'basic';
    }
}
