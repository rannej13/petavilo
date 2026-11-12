@extends('site::layouts.app')

@php
    $isPaid = $business->isPaid();
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => $business->getAttribute('type') === 'vets' ? 'VeterinaryCare' : 'LocalBusiness',
        'name' => (string) $business->getAttribute('name'),
        'telephone' => $business->getAttribute('phone'),
        'url' => $business->websiteUrl(),
        'image' => $business->logoUrl() ?? ($business->photoUrls()[0] ?? null),
        'openingHours' => $business->getAttribute('opening_hours') ? preg_split('/\R/', (string) $business->getAttribute('opening_hours')) : null,
        'sameAs' => $business->getAttribute('social_url') ? [$business->getAttribute('social_url')] : null,
        'geo' => $business->getAttribute('latitude') !== null ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $business->getAttribute('latitude'), 'longitude' => (float) $business->getAttribute('longitude')] : null,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $business->getAttribute('address_line'),
            'addressLocality' => $business->getAttribute('town'),
            'postalCode' => $business->getAttribute('postcode'),
            'addressCountry' => (string) $business->getAttribute('country'),
        ]),
    ]);
@endphp

@section('title', $business->getAttribute('name').($business->getAttribute('town') ? ', '.$business->getAttribute('town') : ''))
@section('description', \Illuminate\Support\Str::limit((string) ($business->getAttribute('description') ?: $typeName.' in '.$business->getAttribute('town')), 155))

@section('content')
<div class="shell shell--narrow page">
    <article class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('directory.index') }}">{{ __('directory::messages.directory') }}</a>
            <span class="breadcrumb__separator">/</span>
            <a href="{{ route('directory.type', $business->getAttribute('type')) }}">{{ $typeName }}</a>
        </nav>

        <header class="business-head">
            @if($logo = $business->logoUrl())<img src="{{ $logo }}" alt="" class="business-logo">@endif
            <div class="stack stack--tight">
            <h1 class="title-page">{{ $business->getAttribute('name') }}</h1>
            <p class="text-meta">
                {{ \Modules\Directory\Models\Business::typeName((string) $business->getAttribute('type'), true) }}
                @if($business->getAttribute('town')) · {{ $business->getAttribute('town') }}@endif
                @if($business->getAttribute('is_verified')) · <span class="badge badge--positive">{{ __('directory::messages.verified') }}</span>@endif
            </p>
            <div class="row row--wrap">
                @if($business->accreditationLabel() && $business->getAttribute('accreditation_verified'))<span class="badge badge--solid">{{ $business->accreditationLabel() }}</span>@endif
                @if($business->getAttribute('emergency_care'))<span class="badge badge--caution">{{ __('directory::messages.emergency_care') }}</span>@endif
                @if($business->getAttribute('home_visits'))<span class="badge">{{ __('directory::messages.home_visits') }}</span>@endif
                @if($business->getAttribute('cat_only'))<span class="badge">{{ __('directory::messages.cat_only') }}</span>@endif
            </div>
            </div>
        </header>

        @if($isPaid && filled($business->getAttribute('new_client_offer')))
            <div class="notice notice--accent"><p class="notice__title">{{ __('directory::messages.offer') }}</p><p>{{ $business->getAttribute('new_client_offer') }}</p></div>
        @endif

        @if($business->photoUrls() !== [])
            <div class="business-photos business-photos--show">
                @foreach($business->photoUrls() as $photo)<img src="{{ $photo }}" alt="{{ $business->getAttribute('name') }}" loading="lazy">@endforeach
            </div>
        @endif

        <section class="card">
            <div class="card__body stack">
                @if(filled($business->getAttribute('description')))
                    <div class="prose">{!! nl2br(e($business->getAttribute('description'))) !!}</div>
                @endif
                <ul class="welfare-list">
                    @if(filled($business->getAttribute('phone')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.phone') }}</span><a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $business->getAttribute('phone')) }}">{{ $business->getAttribute('phone') }}</a></li>
                    @endif
                    @if($business->websiteUrl())
                        <li class="welfare-list__item"><span>{{ __('directory::messages.website') }}</span><a class="text-link" href="{{ route('directory.out', [$business, 'website']) }}" rel="noopener {{ $isPaid ? '' : 'nofollow' }}" target="_blank">{{ parse_url($business->websiteUrl(), PHP_URL_HOST) }}</a></li>
                    @endif
                    @if(filled($business->getAttribute('email')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.email') }}</span><a class="text-link" href="mailto:{{ $business->getAttribute('email') }}">{{ $business->getAttribute('email') }}</a></li>
                    @endif
                    @if(filled($business->getAttribute('address_line')) || filled($business->getAttribute('postcode')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.address') }}</span><span>{{ collect([$business->getAttribute('address_line'), $business->getAttribute('town'), $business->getAttribute('postcode')])->filter()->implode(', ') }}</span></li>
                    @endif
                    @if(filled($business->getAttribute('licence_number')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.licence_number') }}</span><span>{{ $business->getAttribute('licence_number') }}</span></li>
                    @endif
                    @if(filled($business->getAttribute('opening_hours')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.opening_hours') }}</span><span>{!! nl2br(e($business->getAttribute('opening_hours'))) !!}</span></li>
                    @endif
                    @if(filled($business->getAttribute('price_from')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.price_from') }}</span><span>{{ $business->getAttribute('price_from') }}</span></li>
                    @endif
                    @if(filled($business->getAttribute('social_url')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.social_url') }}</span><a class="text-link" href="{{ \Illuminate\Support\Str::startsWith((string) $business->getAttribute('social_url'), 'http') ? $business->getAttribute('social_url') : 'https://'.$business->getAttribute('social_url') }}" rel="noopener nofollow" target="_blank">{{ parse_url((string) $business->getAttribute('social_url'), PHP_URL_HOST) ?: $business->getAttribute('social_url') }}</a></li>
                    @endif
                </ul>
                @if($business->serviceList() !== [])
                    <div class="stack stack--tight">
                        <h2 class="card__title">{{ __('directory::messages.services') }}</h2>
                        <div class="row row--wrap">@foreach($business->serviceList() as $service)<span class="pill">{{ $service }}</span>@endforeach</div>
                    </div>
                @endif
                @if($business->bookingUrl())
                    <a href="{{ route('directory.out', [$business, 'booking']) }}" class="button button--primary" rel="noopener nofollow" target="_blank">{{ __('directory::messages.book_online') }}</a>
                @endif
            </div>
        </section>

        @if($business->getAttribute('latitude') !== null && $business->getAttribute('longitude') !== null)
            @php
                $lat = (float) $business->getAttribute('latitude');
                $lng = (float) $business->getAttribute('longitude');
                $bbox = implode(',', [$lng - 0.012, $lat - 0.007, $lng + 0.012, $lat + 0.007]);
            @endphp
            <section class="stack stack--tight">
                <h2 class="card__title">{{ __('directory::messages.map') }}</h2>
                <div class="mini-map">
                    <iframe src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&amp;layer=mapnik&amp;marker={{ $lat }},{{ $lng }}" title="{{ __('directory::messages.map') }}" loading="lazy" referrerpolicy="no-referrer"></iframe>
                </div>
                <p class="text-meta"><a class="text-link" href="https://www.openstreetmap.org/directions?to={{ $lat }}%2C{{ $lng }}" target="_blank" rel="noopener nofollow">{{ __('directory::messages.directions') }}</a></p>
            </section>
        @endif

        @unless($isPaid)
            <x-ads::slot name="directory"/>
        @endunless

        @if(! $business->isClaimed() && filled($business->getAttribute('source')))
            <aside class="notice" role="note">
                <p>{{ __('directory::messages.data_note') }}</p>
                <p><a class="text-link" href="mailto:{{ \Modules\CatMarket\Support\CatMarketSettings::contactEmail() }}?subject={{ rawurlencode('Remove listing: '.$business->getAttribute('name').' (#'.$business->getKey().')') }}">{{ __('directory::messages.remove_listing') }}</a></p>
            </aside>
        @endif

        @unless($business->isClaimed())
            <section class="card">
                <div class="card__head"><h2 class="card__title">{{ __('directory::messages.claim_title') }}</h2></div>
                <div class="card__body stack">
                    <p class="text-muted">{{ __('directory::messages.claim_lead') }}</p>
                    <ul class="check-list">@foreach(array_slice(__('directory::messages.benefits_free'), 0, 5) as $line)<li>{{ $line }}</li>@endforeach</ul>
                    @auth
                        @if($hasPendingClaim)
                            <p class="badge badge--caution">{{ __('directory::messages.claim_pending') }}</p>
                        @else
                            <form method="POST" action="{{ route('directory.claim', [$business->getAttribute('type'), $business->getAttribute('slug')]) }}" class="stack">
                                @csrf
                                <div class="field__row field__row--two">
                                    <div class="field">
                                        <label class="field__label" for="claim-role">{{ __('directory::messages.role') }}</label>
                                        <input id="claim-role" name="role_at_business" class="input" required maxlength="80" placeholder="Owner, practice manager...">
                                    </div>
                                    <div class="field">
                                        <label class="field__label" for="claim-phone">{{ __('directory::messages.phone') }}</label>
                                        <input id="claim-phone" name="phone" class="input" required maxlength="40" type="tel">
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="claim-message">{{ __('directory::messages.message') }}</label>
                                    <textarea id="claim-message" name="message" class="textarea" rows="3" maxlength="1000"></textarea>
                                </div>
                                <button class="button button--secondary" type="submit">{{ __('directory::messages.claim_submit') }}</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="button button--secondary">{{ __('directory::messages.claim_submit') }}</a>
                    @endauth
                </div>
            </section>
        @endunless
    </article>
</div>
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection
