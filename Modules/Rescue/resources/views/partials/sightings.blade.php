@php
    $sightings = \Modules\Rescue\Models\PetSighting::query()->where('listing_id', $listing->getKey())->latest('seen_at')->limit(30)->get();
    $isOwner = auth()->check() && (int) auth()->id() === (int) $listing->getAttribute('user_id');
    $isAdmin = auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin');
    $visible = $sightings->filter(fn ($s) => ! $s->getAttribute('is_hidden') || $isOwner || $isAdmin);
    $points = \Modules\Rescue\Models\PetSighting::mapPoints((int) $listing->getKey());
    $active = $listing->statusValue() === 'active';
@endphp

<section class="card" id="sightings"><div class="card__body stack">
    <div class="row row--between row--wrap">
        <h2 class="card__title">Seen this pet?</h2>
        <a href="{{ route('rescue.sightings.poster', $listing->getKey()) }}" class="button button--secondary button--small" target="_blank" rel="noopener">Print a poster</a>
    </div>
    <p class="text-muted">Tell the owner where and when. Pins on the public map are rounded to about 100 m.</p>

    <div class="sightings-map" data-sightings-map data-points='@json($points)' data-lat="{{ $listing->getAttribute('latitude') }}" data-lng="{{ $listing->getAttribute('longitude') }}" @if($active && ! $isOwner) data-pick-form="sighting-form" @endif role="region" aria-label="Map of sightings"></div>

    @if($visible->isNotEmpty())
        <div class="data-list">
            @foreach($visible as $sighting)
                <div class="data-row"><div class="data-row__main stack stack--tight">
                    <p class="data-row__title">{{ $sighting->getAttribute('place') }}</p>
                    <div class="data-row__meta">
                        <span>{{ $sighting->getAttribute('seen_at')->isoFormat('ddd D MMM, H:mm') }}</span>
                        <span>{{ $sighting->getAttribute('seen_at')->diffForHumans() }}</span>
                        @if($sighting->getAttribute('is_hidden'))<span class="badge">Hidden</span>@endif
                    </div>
                    @if($sighting->getAttribute('note'))<p>{{ $sighting->getAttribute('note') }}</p>@endif
                    @if($isOwner || $isAdmin)
                        <p class="text-meta">From {{ $sighting->getAttribute('reporter_name') ?: 'someone' }}@if($sighting->getAttribute('reporter_contact')) · {{ $sighting->getAttribute('reporter_contact') }}@endif</p>
                        <form method="POST" action="{{ route('rescue.sightings.hide', $sighting->getKey()) }}">@csrf<button type="submit" class="text-link" style="background: none; border: 0; padding: 0; font: inherit; cursor: pointer">{{ $sighting->getAttribute('is_hidden') ? 'Show on map' : 'Hide from map' }}</button></form>
                    @endif
                </div></div>
            @endforeach
        </div>
    @endif

    @if($active && ! $isOwner)
        <form method="POST" action="{{ route('rescue.sightings.store', $listing->getKey()) }}" id="sighting-form" class="stack">
            @csrf
            {{ \App\Support\SpamGuard::fields() }}
            <input type="hidden" name="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" value="{{ old('longitude') }}">
            <div class="row row--wrap"><button type="button" class="button button--ghost button--small" data-use-location>Use my location</button><span class="text-meta" data-pick-status>Or tap the map where you saw them.</span></div>
            <div class="field__row field__row--two">
                <div class="field"><label class="field__label" for="sighting-place">Where</label><input id="sighting-place" name="place" class="input" required maxlength="160" value="{{ old('place') }}" placeholder="e.g. Back lane behind Union Street">@error('place')<p class="field__error">{{ $message }}</p>@enderror</div>
                <div class="field"><label class="field__label" for="sighting-when">When</label><input id="sighting-when" name="seen_at" type="datetime-local" class="input" required max="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('seen_at', now()->format('Y-m-d\TH:i')) }}">@error('seen_at')<p class="field__error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="field"><label class="field__label" for="sighting-note">What did you see? (optional)</label><input id="sighting-note" name="note" class="input" maxlength="500" value="{{ old('note') }}" placeholder="Heading towards the park, looked healthy, wouldn't come to me"></div>
            <div class="field__row field__row--two">
                <div class="field"><label class="field__label" for="sighting-name">Your name (optional)</label><input id="sighting-name" name="reporter_name" class="input" maxlength="80" value="{{ old('reporter_name', auth()->user()?->getAttribute('name')) }}"></div>
                <div class="field"><label class="field__label" for="sighting-contact">Phone or email (optional)</label><input id="sighting-contact" name="reporter_contact" class="input" maxlength="120" value="{{ old('reporter_contact') }}"><p class="field__hint">Only the owner sees this.</p></div>
            </div>
            <div class="row"><button type="submit" class="button button--primary">Send sighting to the owner</button></div>
        </form>
    @elseif($isOwner)
        <aside class="notice notice--accent"><p class="notice__title">Spread the word</p><p>Print the poster and put it up near where your pet was last seen. The QR code brings people straight here to report sightings, and you get a notification for each one.</p></aside>
    @endif
</div></section>
