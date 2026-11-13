@extends('site::layouts.app')

@section('title', 'Adopt a pet from a verified rescue')
@section('description', 'Cats, dogs, rabbits and other pets looking for homes with verified rescues and charities in the UK and US.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Adopt</p>
            <h1 class="title-page">Adopt a pet from a verified rescue</h1>
            <p class="text-lead">Every animal here is listed by a rescue or charity we have checked. They assess their animals, match you carefully and support you after adoption.</p>
        </header>

        @include('rescue::partials.species-filter')

        @if($listings->isNotEmpty())
            <div class="grid grid--listings">
                @foreach($listings as $listing)
                    <div class="stack stack--tight">
                        <x-ui.listing-card :listing="$listing"/>
                        @auth
                            <a href="{{ route('rescue.apply', $listing->getKey()) }}" class="button button--secondary button--small">Apply to adopt</a>
                        @else
                            <a href="{{ route('login') }}" class="button button--ghost button--small">Log in to apply</a>
                        @endauth
                    </div>
                @endforeach
            </div>
            {{ $listings->links('components.pagination') }}
        @else
            <x-ui.empty-state icon="heart" title="No rescue animals listed here yet" text="We are inviting rescues near you to join. Run a rescue? List your animals free.">
                <a href="{{ route('rescue.join') }}" class="button button--primary">Add your rescue</a>
            </x-ui.empty-state>
        @endif

        @if($privateListings->isNotEmpty())
            <section class="stack">
                <div class="row row--between">
                    <h2 class="title-section">Pets being rehomed by their owners</h2>
                    @if($rehomingCategoryId)
                        <a class="text-link" href="{{ route('listings.index', ['category' => $rehomingCategoryId]) }}">See all</a>
                    @endif
                </div>
                <p class="text-muted">These are private adverts, not from a rescue. Meet the pet, ask for vet records and never pay before you visit.</p>
                <div class="grid grid--listings">
                    @foreach($privateListings as $listing)
                        <x-ui.listing-card :listing="$listing"/>
                    @endforeach
                </div>
            </section>
        @endif

        <aside class="notice notice--accent">
            <p class="notice__title">Run a rescue or charity?</p>
            <p>List animals for adoption free, take applications online and get a Verified rescue badge. <a class="text-link" href="{{ route('rescue.join') }}">Add your rescue</a></p>
        </aside>
    </div>
</div>
@endsection
