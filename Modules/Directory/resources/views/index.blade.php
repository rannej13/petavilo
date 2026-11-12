@extends('site::layouts.app')

@section('title', __('directory::messages.directory'))
@section('description', __('directory::messages.directory_lead'))

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <header class="row row--between row--wrap">
            <div class="stack stack--tight">
                <h1 class="title-page">{{ __('directory::messages.directory') }}</h1>
                <p class="text-lead">{{ __('directory::messages.directory_lead') }}</p>
            </div>
            <a href="{{ route('directory.create') }}" class="button button--primary">{{ __('directory::messages.add_business') }}</a>
        </header>

        <div class="chip-nav">
            <a href="{{ request()->fullUrlWithQuery(['region' => 'GB', 'page' => null]) }}" class="pill {{ $region === 'GB' ? 'is-active' : '' }}">UK</a>
            <a href="{{ request()->fullUrlWithQuery(['region' => 'US', 'page' => null]) }}" class="pill {{ $region === 'US' ? 'is-active' : '' }}">US</a>
        </div>

        <div class="grid grid--categories">
            @foreach($types as $slug => $name)
                <a href="{{ route('directory.type', ['type' => $slug, 'region' => $region]) }}" class="card">
                    <div class="card__body stack stack--tight">
                        <p class="title-card">{{ $name }}</p>
                        <p class="text-meta">{{ __('directory::messages.listed_in', ['count' => $counts[$slug] ?? 0]) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <x-ads::slot name="directory"/>

        @if($featured->isNotEmpty())
            <section class="section">
                <div class="section__head"><h2 class="title-section">{{ __('directory::messages.featured') }}</h2></div>
                <div class="grid grid--three">
                    @foreach($featured as $business)
                        @include('directory::partials.card', ['business' => $business])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
