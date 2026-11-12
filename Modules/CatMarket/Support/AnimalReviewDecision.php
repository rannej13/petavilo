<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

use Modules\CatMarket\Models\AnimalListingReview;
use Modules\Listing\Models\Listing;
use Modules\Listing\States\ActiveListingStatus;
use Modules\Listing\States\ExpiredListingStatus;
use Modules\Notification\Models\UserNotification;

final class AnimalReviewDecision
{
    public static function approve(AnimalListingReview $review, int $adminId): bool
    {
        $listing = Listing::query()->find((int) $review->getAttribute('listing_id'));

        if (! $listing instanceof Listing) {
            return false;
        }

        $listing->forceFill([
            'status' => ActiveListingStatus::class,
            'expires_at' => $listing->getAttribute('expires_at') ?? now()->addDays(30),
        ])->save();

        AnimalListingReview::decide((int) $listing->getKey(), AnimalListingReview::APPROVED, $adminId);
        self::notify($listing, __('catmarket::messages.approved_title'), __('catmarket::messages.approved_body'));

        return true;
    }

    public static function reject(AnimalListingReview $review, int $adminId, string $note): bool
    {
        $listing = Listing::query()->find((int) $review->getAttribute('listing_id'));

        if (! $listing instanceof Listing) {
            return false;
        }

        $listing->forceFill(['status' => ExpiredListingStatus::class])->save();

        AnimalListingReview::decide((int) $listing->getKey(), AnimalListingReview::REJECTED, $adminId, $note);
        self::notify($listing, __('catmarket::messages.rejected_title'), __('catmarket::messages.rejected_body', ['reason' => $note]));

        return true;
    }

    private static function notify(Listing $listing, string $title, string $body): void
    {
        $ownerId = $listing->getAttribute('user_id');

        if ($ownerId === null) {
            return;
        }

        UserNotification::publish(
            (int) $ownerId,
            UserNotification::TYPE_LISTING,
            $title,
            $body,
            route('panel.listings.edit', $listing),
        );
    }
}
