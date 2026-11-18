@extends('site::layouts.app')

@section('title', 'Pet of the Week')
@section('description', 'Vote for Pet of the Week on Petavilo. Enter your own pet free. One vote per member, every photo checked.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow"><a href="{{ route('tv.index') }}" class="text-link">Petavilo TV</a></p>
            <h1 class="title-page">Pet of the Week</h1>
            <p class="text-lead">Vote for your favourite. Voting closes {{ $closes->isoFormat('dddd D MMMM') }} at midnight, and the winner is announced on Monday morning.</p>
            <div class="row row--wrap">
                @if($myEntry)
                    <span class="pill">Your entry: {{ $myEntry->getAttribute('pet_name') }} · {{ \Modules\Tv\Models\PetOfWeekEntry::statuses()[$myEntry->getAttribute('status')] ?? '' }}</span>
                @else
                    <a href="{{ route('tv.potw.enter') }}" class="button button--primary">Enter your pet</a>
                @endif
            </div>
        </header>

        @if($entries->isEmpty())
            <x-ui.empty-state icon="heart" title="No pets in the running yet this week" text="Be the first: enter a photo of your pet. We check every entry, usually within a day.">
                <a href="{{ route('tv.potw.enter') }}" class="button button--primary">Enter your pet</a>
            </x-ui.empty-state>
        @else
            <div class="potw-grid">
                @foreach($entries as $entry)
                    <article class="card"><div class="card__body stack stack--tight">
                        <img src="{{ $entry->photoUrl() }}" alt="{{ $entry->getAttribute('pet_name') }}" class="potw-photo" loading="lazy">
                        <h2 class="title-card">{{ $entry->getAttribute('pet_name') }}</h2>
                        @if($entry->getAttribute('caption'))<p class="text-muted">{{ $entry->getAttribute('caption') }}</p>@endif
                        <p class="text-muted" style="font-size: var(--text-xs)">{{ $entry->ownerFirstName() }}'s {{ strtolower(\Modules\PetTools\Models\Pet::speciesOptions()[$entry->getAttribute('species')] ?? 'pet') }} · {{ $entry->getAttribute('votes_count') }} {{ \Illuminate\Support\Str::plural('vote', $entry->getAttribute('votes_count')) }}</p>
                        @auth
                            @if($myVote === (int) $entry->getKey())
                                <span class="pill is-active">Your vote</span>
                            @elseif($myVote === null && (int) $entry->getAttribute('user_id') !== (int) auth()->id())
                                <form method="POST" action="{{ route('tv.potw.vote', $entry) }}">@csrf<button type="submit" class="button button--secondary button--block">Vote for {{ $entry->getAttribute('pet_name') }}</button></form>
                            @endif
                        @else
                            <a href="{{ route('login', ['redirect' => route('tv.potw')]) }}" class="button button--secondary button--block">Sign in to vote</a>
                        @endauth
                    </div></article>
                @endforeach
            </div>
        @endif

        @if($winners->isNotEmpty())
            <section class="stack">
                <h2 class="title-section">Hall of fame</h2>
                <div class="potw-grid">
                    @foreach($winners as $winner)
                        <article class="stack stack--tight">
                            <img src="{{ $winner->photoUrl() }}" alt="{{ $winner->getAttribute('pet_name') }}" class="potw-photo" loading="lazy">
                            <p class="tv-card__title">{{ $winner->getAttribute('pet_name') }}</p>
                            <p class="text-muted" style="font-size: var(--text-xs)">Week of {{ $winner->getAttribute('week_start')->isoFormat('D MMM YYYY') }} · {{ $winner->getAttribute('votes_count') }} votes</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <aside class="notice"><p class="notice__title">How it stays fair</p><p>One vote per signed-in member each week, no voting for your own pet, and every photo is checked by a person before it appears. We never accept photos of animals in distress or doing anything risky.</p></aside>
    </div>
</div>
@endsection
