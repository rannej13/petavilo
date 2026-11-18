<?php

declare(strict_types=1);

namespace Modules\Rescue\Models;

use Illuminate\Database\Eloquent\Model;

class PetSighting extends Model
{
    protected $fillable = ['reporter_name', 'reporter_contact', 'latitude', 'longitude', 'place', 'seen_at', 'note'];

    protected $casts = [
        'seen_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_hidden' => 'boolean',
    ];

    public const LOST_CATEGORY = 'lost-and-found-lost-pets';

    public static function isLostListing(object $listing): bool
    {
        return (string) ($listing->category?->slug ?? '') === self::LOST_CATEGORY;
    }

    /**
     * Points for the public map: approximate (3 decimals, about 100 m) so a
     * reporter's exact doorstep is never published.
     *
     * @return list<array{lat: float, lng: float, when: string, place: string}>
     */
    public static function mapPoints(int $listingId): array
    {
        return static::query()
            ->where('listing_id', $listingId)
            ->where('is_hidden', false)
            ->whereNotNull('latitude')
            ->latest('seen_at')
            ->limit(50)
            ->get()
            ->map(static fn (self $s): array => [
                'lat' => round((float) $s->getAttribute('latitude'), 3),
                'lng' => round((float) $s->getAttribute('longitude'), 3),
                'when' => $s->getAttribute('seen_at')->isoFormat('ddd D MMM, H:mm'),
                'place' => (string) $s->getAttribute('place'),
            ])
            ->all();
    }
}
