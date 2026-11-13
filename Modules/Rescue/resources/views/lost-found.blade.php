@extends('site::layouts.app')

@section('title', 'Lost and found pets')
@section('description', 'Report a lost or found cat, dog or other pet free, and search lost and found pets near you in the UK and US.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <header class="row row--between row--wrap">
            <div class="stack stack--tight">
                <p class="text-eyebrow">Lost &amp; found</p>
                <h1 class="title-page">Lost and found pets</h1>
                <p class="text-lead">Posting is always free. Share your advert link on local groups too.</p>
            </div>
            <div class="row">
                <a href="{{ route('panel.listings.create') }}" class="button button--primary">Report a lost or found pet</a>
                <a href="{{ route('rescue.found-animal') }}" class="button button--ghost">Found an injured animal?</a>
            </div>
        </header>

        <div class="chip-nav">
            <a href="{{ request()->fullUrlWithQuery(['type' => null, 'page' => null]) }}" class="pill {{ $type === null ? 'is-active' : '' }}">Lost and found</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'lost', 'page' => null]) }}" class="pill {{ $type === 'lost' ? 'is-active' : '' }}">Lost</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'found', 'page' => null]) }}" class="pill {{ $type === 'found' ? 'is-active' : '' }}">Found</a>
        </div>
        @include('rescue::partials.species-filter')

        @if($listings->isNotEmpty())
            <div class="grid grid--listings">
                @foreach($listings as $listing)
                    <x-ui.listing-card :listing="$listing"/>
                @endforeach
            </div>
            {{ $listings->links('components.pagination') }}
        @else
            <x-ui.empty-state icon="map-pin" title="No reports here right now" text="If your pet is missing, post a free advert straight away. The first 24 hours matter most."/>
        @endif

        <section class="grid grid--three">
            <div class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">If your pet is missing</h2>
                <p class="text-muted">Search nearby sheds and garages, tell neighbours, call local vets and your council's dog warden, and update the microchip database so they know your pet is missing.</p>
            </div></div>
            <div class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">If you found a pet</h2>
                <p class="text-muted">Any vet can scan for a microchip, usually free. In the UK, found dogs must be reported to the council dog warden. Many cats roam, so check for an owner before taking one in.</p>
            </div></div>
            <div class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">Stay safe</h2>
                <p class="text-muted">Never post the microchip number or your home address. Ask anyone claiming a pet to describe a marking you left out, and meet in a public place or at a vet.</p>
            </div></div>
        </section>
    </div>
</div>
@endsection
