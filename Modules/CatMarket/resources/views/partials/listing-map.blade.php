@php
    $lat = $listing->getAttribute('latitude');
    $lng = $listing->getAttribute('longitude');
@endphp
@if($lat !== null && $lng !== null)
    @php
        $lat = round((float) $lat, 2);
        $lng = round((float) $lng, 2);
        $bbox = implode(',', [$lng - 0.03, $lat - 0.018, $lng + 0.03, $lat + 0.018]);
        $embed = 'https://www.openstreetmap.org/export/embed.html?bbox='.$bbox.'&layer=mapnik';
        $link = 'https://www.openstreetmap.org/?mlat='.$lat.'&mlon='.$lng.'#map=13/'.$lat.'/'.$lng;
    @endphp
    <div class="stack stack--tight">
        <h2 class="card__title">{{ __('catmarket::messages.map_title') }}</h2>
        <div class="mini-map">
            <iframe src="{{ $embed }}" title="{{ __('catmarket::messages.map_title') }}" loading="lazy" referrerpolicy="no-referrer"></iframe>
            <span class="mini-map__circle" aria-hidden="true"></span>
        </div>
        <p class="text-meta">{{ __('catmarket::messages.map_note') }} <a href="{{ $link }}" target="_blank" rel="noopener nofollow">{{ __('catmarket::messages.map_open') }}</a></p>
    </div>
@endif
