<?php

declare(strict_types=1);

namespace Modules\CatMarket\Observers;

use Illuminate\Validation\ValidationException;
use Modules\CatMarket\Models\AnimalListingReview;
use Modules\CatMarket\Support\WelfareRules;
use Modules\Listing\Models\Listing;
use Modules\Listing\States\ListingStatus;
use Modules\Listing\States\PendingListingStatus;

final class AnimalListingObserver
{
    private const CONTENT_ATTRIBUTES = ['title', 'description', 'custom_fields', 'category_id', 'price'];

    private static array $queuedReasons = [];

    public function saving(Listing $listing): void
    {
        $categoryId = $listing->getAttribute('category_id') === null ? null : (int) $listing->getAttribute('category_id');
        $title = (string) $listing->getAttribute('title');
        $description = (string) $listing->getAttribute('description');
        $values = (array) ($listing->getAttribute('custom_fields') ?? []);

        if (self::actorIsTrusted()) {
            return;
        }

        $contentChanged = ! $listing->exists || $listing->isDirty(self::CONTENT_ATTRIBUTES);
        $errors = $contentChanged ? WelfareRules::errors($categoryId, $values, $title, $description, (string) $listing->getAttribute('country')) : [];

        if ($errors !== [] && (bool) config('catmarket.enforce_rules', false)) {
            throw ValidationException::withMessages(self::prefixed($errors));
        }

        \Modules\CatMarket\Support\SpamSignals::guardRate($listing);
        $spam = $contentChanged && ! (bool) $listing->getAttribute('is_example')
            ? \Modules\CatMarket\Support\SpamSignals::reasons($listing)
            : [];

        if (! WelfareRules::isAnimalCategory($categoryId) && $errors === []) {
            if ($spam !== []) {
                self::$queuedReasons[spl_object_id($listing)] = array_values(array_unique($spam));
            }

            return;
        }

        $original = $listing->getOriginal('status');
        $originalValue = $original instanceof ListingStatus ? $original->getValue() : (string) $original;
        $goingLive = $listing->exists && $listing->statusValue() === 'active' && $originalValue !== 'active';

        if (! $contentChanged && ! $goingLive) {
            return;
        }

        $premoderate = (bool) config('catmarket.premoderate', false) && WelfareRules::isAnimalCategory($categoryId);
        $reasons = [
            ...array_values($errors),
            ...WelfareRules::holdReasons($categoryId, $values, $title, $description),
            ...$spam,
        ];

        if ($premoderate) {
            $listing->setAttribute('status', PendingListingStatus::class);
        } else {
            $reasons = array_values(array_diff($reasons, [__('catmarket::messages.hold_animal')]));
        }

        if ($reasons !== []) {
            self::$queuedReasons[spl_object_id($listing)] = array_values(array_unique($reasons));
        }
    }

    public function saved(Listing $listing): void
    {
        $key = spl_object_id($listing);

        if (! array_key_exists($key, self::$queuedReasons)) {
            return;
        }

        $reasons = self::$queuedReasons[$key];
        unset(self::$queuedReasons[$key]);

        AnimalListingReview::submit(
            (int) $listing->getKey(),
            $listing->getAttribute('user_id') === null ? null : (int) $listing->getAttribute('user_id'),
            $reasons,
        );
    }

    private static function actorIsTrusted(): bool
    {
        $user = auth()->user();

        if ($user !== null) {
            return method_exists($user, 'hasRole') && $user->hasRole('admin');
        }

        return app()->runningInConsole();
    }

    private static function prefixed(array $errors): array
    {
        $messages = [];

        foreach ($errors as $key => $message) {
            $field = in_array($key, ['title', 'description'], true) ? $key : 'customFieldValues.'.$key;
            $messages[$field] = [$message];
        }

        return $messages;
    }
}
