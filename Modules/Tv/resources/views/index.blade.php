@extends('site::layouts.app')

@section('title', 'Petavilo TV: funny and cute pet videos')
@section('description', 'Funny cats, silly dogs and cute moments in short videos, plus the weekly Pet of the Week. Free to watch, no sign-up.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            @if($greeting)<p class="text-eyebrow">{{ $greeting }}</p>@else<p class="text-eyebrow">Petavilo TV</p>@endif
            <h1 class="title-page">Funny, cute and clever pets</h1>
            <p class="text-lead">Short pet videos, free to watch with no sign-up. Videos load only when you press play.</p>
        </header>

        @if(count($categories) > 1)
            <nav class="chip-nav" aria-label="Video categories">
                <a href="{{ route('tv.index') }}" class="pill {{ $category === '' ? 'is-active' : '' }}">All</a>
                @foreach($categories as $key => $label)
                    <a href="{{ route('tv.index', ['c' => $key]) }}" class="pill {{ $category === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>
        @endif

        @if($videos->isEmpty())
            <x-ui.empty-state icon="video" title="New videos are on the way" text="Follow Petavilo on TikTok, Instagram and YouTube for the first ones. Meanwhile, enter your pet for Pet of the Week.">
                <a href="{{ route('tv.potw') }}" class="button button--primary">Pet of the Week</a>
            </x-ui.empty-state>
        @else
            <div class="tv-grid">
                @foreach($videos as $video)
                    <article class="tv-card">
                        <div class="tv-frame">
                            <button type="button" class="tv-play" data-video-embed="{{ $video->embedUrl() }}" data-video-title="{{ $video->getAttribute('title') }}" data-video-played="{{ route('tv.played', $video) }}" aria-label="Play: {{ $video->getAttribute('title') }}">
                                @if($video->thumbnailUrl())<img src="{{ $video->thumbnailUrl() }}" alt="" loading="lazy" referrerpolicy="no-referrer">@endif
                                <span class="tv-play__icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
                                <span class="tv-play__note">Plays from {{ $video->platformLabel() }}</span>
                            </button>
                        </div>
                        <p class="tv-card__title">{{ $video->getAttribute('title') }}</p>
                        @if($video->getAttribute('credit'))<p class="text-muted" style="font-size: var(--text-xs)">{{ $video->getAttribute('credit') }}</p>@endif
                        @if($video->nextSteps() !== [])
                            <div class="tv-next">
                                @foreach($video->nextSteps() as $step)
                                    <a href="{{ $step['url'] }}" class="pill">{{ $step['label'] }}</a>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        <section class="card"><div class="card__body grid grid--split" style="align-items: center">
            <div class="stack stack--tight">
                <p class="text-eyebrow">Pet of the Week</p>
                @if($winner)
                    <h2 class="title-section">Congratulations, {{ $winner->getAttribute('pet_name') }}!</h2>
                    <p class="text-muted">Voted Pet of the Week by Petavilo members. Enter your own pet: it's free and takes a minute.</p>
                @else
                    <h2 class="title-section">Could your pet be Pet of the Week?</h2>
                    <p class="text-muted">Post a photo, get votes, win a spot on Petavilo TV. One vote per member, every photo checked.</p>
                @endif
                <div class="row row--wrap"><a href="{{ route('tv.potw') }}" class="button button--primary">Vote and enter</a></div>
            </div>
            @if($winner)<img src="{{ $winner->photoUrl() }}" alt="{{ $winner->getAttribute('pet_name') }}, Pet of the Week" class="potw-photo" loading="lazy">@endif
        </div></section>

        <section class="grid grid--three">
            <a href="{{ route('pettools.poison') }}" class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Saw a pet eat something odd?</p><h2 class="title-card">Is it safe for my pet?</h2><p class="text-muted">Check foods, plants and household products.</p></div></a>
            @if(\Illuminate\Support\Facades\Route::has('pettools.match'))
                <a href="{{ route('pettools.match') }}" class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Thinking of getting one?</p><h2 class="title-card">Is this pet right for me?</h2><p class="text-muted">A 2-minute quiz with honest yearly costs.</p></div></a>
            @endif
            <a href="{{ route('rescue.adopt') }}" class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Ready for the real thing?</p><h2 class="title-card">Adopt from a verified rescue</h2><p class="text-muted">Animals waiting near you.</p></div></a>
        </section>
    </div>
</div>
@endsection
