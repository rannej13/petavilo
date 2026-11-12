@extends('site::layouts.app')

@section('title', $typeName.($town !== '' ? ' in '.$town : ''))
@section('description', $typeName.' for cats'.($town !== '' ? ' in '.$town : ' across the '.($region === 'US' ? 'US' : 'UK')).'.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">{{ __('site::messages.home') }}</a>
            <span class="breadcrumb__separator">/</span>
            <a href="{{ route('directory.index') }}">{{ __('directory::messages.directory') }}</a>
            <span class="breadcrumb__separator">/</span>
            <span>{{ $typeName }}</span>
        </nav>

        <h1 class="title-page">{{ $typeName }}@if($town !== '') in {{ $town }}@endif</h1>

        <div class="chip-nav">
            <a href="{{ request()->fullUrlWithQuery(['region' => 'GB', 'page' => null]) }}" class="pill {{ $region === 'GB' ? 'is-active' : '' }}">UK</a>
            <a href="{{ request()->fullUrlWithQuery(['region' => 'US', 'page' => null]) }}" class="pill {{ $region === 'US' ? 'is-active' : '' }}">US</a>
        </div>

        <div class="chip-nav">
            @foreach($types as $slug => $name)
                <a href="{{ route('directory.type', ['type' => $slug, 'region' => $region]) }}" class="pill {{ $slug === $type ? 'is-active' : '' }}">{{ $name }}</a>
            @endforeach
        </div>

        <form method="GET" class="field__row field__row--two" role="search">
            <input type="hidden" name="region" value="{{ $region }}">
            <div class="field">
                <label class="field__label" for="directory-q">{{ __('directory::messages.search') }}</label>
                <input id="directory-q" type="search" name="q" value="{{ $search }}" class="input">
            </div>
            <div class="field">
                <label class="field__label" for="directory-town">{{ __('directory::messages.town') }}</label>
                <input id="directory-town" name="town" class="input" list="directory-towns" value="{{ $town }}" placeholder="{{ __('directory::messages.all_towns') }}" autocomplete="off">
                <datalist id="directory-towns">
                    @foreach($towns as $option)
                        <option value="{{ $option }}"></option>
                    @endforeach
                </datalist>
            </div>
            <button type="submit" class="button button--secondary">{{ __('directory::messages.filter') }}</button>
        </form>

        @if($businesses->isNotEmpty())
            <div class="grid grid--three">
                @foreach($businesses as $business)
                    @include('directory::partials.card', ['business' => $business])
                    @if($loop->iteration === 6)
                        <x-ads::slot name="directory" style="grid-column: 1 / -1"/>
                    @endif
                @endforeach
            </div>
            {{ $businesses->links('components.pagination') }}
        @else
            <x-ui.empty-state icon="building" :title="__('directory::messages.none')" :text="__('directory::messages.none_hint')">
                <a href="{{ route('directory.create') }}" class="button button--secondary">{{ __('directory::messages.add_business') }}</a>
            </x-ui.empty-state>
        @endif
    </div>
</div>
@endsection
