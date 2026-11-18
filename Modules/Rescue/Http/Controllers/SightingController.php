<?php

declare(strict_types=1);

namespace Modules\Rescue\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\SpamGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Listing\Models\Listing;
use Modules\Notification\Models\UserNotification;
use Modules\Rescue\Models\PetSighting;

class SightingController extends Controller
{
    public function store(Request $request, int $listing): RedirectResponse
    {
        $record = $this->lostListing($listing);
        SpamGuard::check($request, 'place');

        $data = $request->validate([
            'place' => ['required', 'string', 'max:160'],
            'seen_at' => ['required', 'date', 'before_or_equal:now', 'after:-60 days'],
            'note' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'reporter_name' => ['nullable', 'string', 'max:80'],
            'reporter_contact' => ['nullable', 'string', 'max:120'],
        ], [
            'seen_at.before_or_equal' => 'The time seen can\'t be in the future.',
        ]);

        $ipHash = hash('sha256', (string) $request->ip().'|'.config('app.key'));

        if (PetSighting::query()->where('listing_id', $record->getKey())->where('ip_hash', $ipHash)->where('created_at', '>', now()->subMinutes(10))->count() >= 3) {
            return back()->with('error', 'Thanks, we have your recent sightings. Please wait a few minutes before adding more.');
        }

        $sighting = new PetSighting($data);
        $sighting->forceFill([
            'listing_id' => $record->getKey(),
            'user_id' => $request->user()?->getKey(),
            'ip_hash' => $ipHash,
            'reporter_name' => $data['reporter_name'] ?? $request->user()?->getAttribute('name'),
        ])->save();

        $ownerId = $record->getAttribute('user_id');

        if ($ownerId !== null && (int) $ownerId !== (int) $request->user()?->getKey()) {
            try {
                $who = trim((string) ($sighting->getAttribute('reporter_name') ?? '')) ?: 'Someone';
                $contact = trim((string) ($sighting->getAttribute('reporter_contact') ?? ''));

                UserNotification::publish(
                    (int) $ownerId,
                    UserNotification::TYPE_LISTING,
                    'New sighting: '.$record->getAttribute('title'),
                    $who.' saw your pet near '.$sighting->getAttribute('place').' on '.$sighting->getAttribute('seen_at')->isoFormat('ddd D MMM [at] H:mm').'.'
                        .($sighting->getAttribute('note') ? ' "'.$sighting->getAttribute('note').'"' : '')
                        .($contact !== '' ? ' Contact: '.$contact.'.' : ''),
                    route('listings.show', $record).'#sightings',
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect(route('listings.show', $record).'#sightings')->with('success', 'Thank you. The owner has been told straight away.');
    }

    public function hide(Request $request, int $sighting): RedirectResponse
    {
        $record = PetSighting::query()->findOrFail($sighting);
        $listing = Listing::query()->findOrFail($record->getAttribute('listing_id'));
        $user = $request->user();

        abort_unless((int) $listing->getAttribute('user_id') === (int) $user->getKey() || (method_exists($user, 'hasRole') && $user->hasRole('admin')), 403);

        $record->forceFill(['is_hidden' => ! $record->getAttribute('is_hidden')])->save();

        return back()->with('success', $record->getAttribute('is_hidden') ? 'Sighting hidden from the public map.' : 'Sighting shown again.');
    }

    public function poster(int $listing): View
    {
        $record = $this->lostListing($listing, false);

        return view('rescue::lost-poster', [
            'listing' => $record,
            'photo' => method_exists($record, 'getFirstMediaUrl') ? ($record->getFirstMediaUrl('listing-images') ?: null) : null,
            'url' => route('listings.show', $record),
        ]);
    }

    private function lostListing(int $id, bool $mustBeActive = true): Listing
    {
        $listing = Listing::query()->with('category')->findOrFail($id);

        abort_unless(PetSighting::isLostListing($listing), 404);

        if ($mustBeActive) {
            abort_unless($listing->statusValue() === 'active', 404);
        }

        return $listing;
    }
}
