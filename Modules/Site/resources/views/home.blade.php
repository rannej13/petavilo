@extends('site::layouts.app')

@section('title', __('site::messages.home_seo_title'))
@section('description', __('site::messages.tagline'))

@section('content')
@php $heroImage = \Modules\CatMarket\Support\SiteImages::url('hero'); @endphp
<section class="hero {{ $heroImage ? 'hero--image' : '' }}" @if($heroImage) style="--hero-image: url('{{ $heroImage }}')" @endif>
    <div class="shell">
        <div class="hero__inner">
            <h1 class="title-hero hero__title">{{ __('site::messages.hero_title') }}</h1>
            <p class="text-lead hero__lead">{{ __('site::messages.hero_lead') }}</p>
            @php $region = \Modules\CatMarket\Support\CatMarketSettings::visitorCountry(); @endphp
            <div class="region-switch" role="group" aria-label="Region">
                <a href="{{ request()->fullUrlWithQuery(['region' => 'GB']) }}" class="region-switch__item {{ $region === 'GB' ? 'is-active' : '' }}">🇬🇧 UK</a>
                <a href="{{ request()->fullUrlWithQuery(['region' => 'US']) }}" class="region-switch__item {{ $region === 'US' ? 'is-active' : '' }}">🇺🇸 US</a>
            </div>

            <form action="{{ route('listings.index') }}" method="GET" class="hero__search" role="search">
                <div class="hero__search-row">
                    <span class="input-affix">
                        <x-ui.icon name="search" class="input-affix__icon"/>
                        <label class="visually-hidden" for="hero-search">{{ __('site::messages.search') }}</label>
                        <input id="hero-search" type="search" name="search" class="input" placeholder="{{ __('site::messages.search_placeholder') }}">
                    </span>
                    <button type="submit" class="button button--primary button--large">{{ __('site::messages.search') }}</button>
                </div>
            </form>

            <div class="hero__stats">
                @foreach($heroStats as [$number, $label])
                    <span class="hero__stat"><strong>{{ $number }}</strong> {{ $label }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

<div class="shell shell--wide page">
    <div class="stack stack--section">
        @if($categories->isNotEmpty())
        <section class="section">
            <div class="section__head">
                <h2 class="title-section">{{ __('site::messages.popular_categories') }}</h2>
                <a href="{{ route('categories.index') }}" class="text-link">{{ __('site::messages.explore_all') }}</a>
            </div>

            <div class="grid grid--categories">
                @foreach($categories->take(12) as $category)
                    <a href="{{ route('listings.index', ['category' => $category->getKey()]) }}" class="category-card">
                        <span class="category-card__icon">
                            @if($category->iconUrl())
                                <img src="{{ $category->iconUrl() }}" alt="" loading="lazy">
                            @else
                                <x-ui.icon name="grid"/>
                            @endif
                        </span>
                        <span class="category-card__name">{{ $category->getAttribute('name') }}</span>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        @if($featuredListings->isNotEmpty())
        <section class="section">
            <div class="section__head">
                <h2 class="title-section">{{ __('site::messages.featured') }}</h2>
                <a href="{{ route('listings.index', ['sort' => 'relevance']) }}" class="text-link">{{ __('site::messages.view_all') }}</a>
            </div>

            <div class="grid grid--listings">
                @foreach($featuredListings as $listing)
                    <x-ui.listing-card :listing="$listing" :favorited="in_array((int) $listing->getKey(), $favoriteListingIds, true)"/>
                @endforeach
            </div>
        </section>
        @endif

        <section class="section">
            <div class="section__head">
                <h2 class="title-section">{{ __('site::messages.recent_listings') }}</h2>
                <a href="{{ route('listings.index', ['sort' => 'newest']) }}" class="text-link">{{ __('site::messages.view_all') }}</a>
            </div>

            @if($recentListings->isNotEmpty())
                <div class="grid grid--listings">
                    @foreach($recentListings as $listing)
                        <x-ui.listing-card :listing="$listing" :favorited="in_array((int) $listing->getKey(), $favoriteListingIds, true)"/>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="tag" :title="__('site::messages.no_listings')" :text="__('site::messages.no_listings_hint')"/>
            @endif
        </section>

        <x-ads::slot name="home"/>

        @include('catmarket::partials.home-extras')

        <section class="promo-banner">
            <h2 class="promo-banner__title">{{ __('site::messages.sell_something') }}</h2>
            <p class="promo-banner__text">{{ __('site::messages.hero_lead') }}</p>
            <div class="row row--wrap">
                <a href="{{ auth()->check() ? route('panel.listings.create') : route('register') }}" class="button button--secondary">
                    {{ __('site::messages.post_listing_cta') }}
                </a>
                <a href="{{ route('promotions.plans') }}" class="button button--ghost" style="color:#fff">
                    {{ __('promotion::messages.plans') }}
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
