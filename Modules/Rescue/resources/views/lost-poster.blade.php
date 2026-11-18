@extends('site::layouts.app')

@section('title', 'Lost: '.$listing->getAttribute('title'))

@section('content')
<div class="shell page">
    <div class="poster stack">
        <div class="row row--wrap no-print" style="justify-content: center">
            <button type="button" class="button button--primary" onclick="window.print()">Print poster</button>
            <a href="{{ $url }}" class="button button--ghost">Back to the advert</a>
        </div>
        <p class="poster__headline">LOST</p>
        <h1 class="title-page">{{ $listing->getAttribute('title') }}</h1>
        @if($photo)<img src="{{ $photo }}" alt="{{ $listing->getAttribute('title') }}" class="poster__photo">@endif
        <p class="text-lead">Last seen near {{ $listing->getAttribute('city') ?: 'here' }}@if($listing->getAttribute('created_at')) · reported {{ $listing->getAttribute('created_at')->isoFormat('D MMMM') }}@endif</p>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $listing->getAttribute('description')), 280) }}</p>
        <div class="grid grid--split" style="align-items: center; text-align: left">
            <div class="stack stack--tight">
                <p class="title-section" style="margin: 0">Seen me? Scan to tell my family</p>
                <p>Or go to {{ preg_replace('#^https?://#', '', $url) }}</p>
                <p class="text-meta">Please don't chase. Note where and when, and report it.</p>
            </div>
            <div data-qr="{{ $url }}" data-qr-label="the lost pet advert" style="max-width: 220px; margin-inline: auto"></div>
        </div>
        <p class="text-meta">Petavilo lost and found · free for everyone</p>
    </div>
</div>
@endsection
