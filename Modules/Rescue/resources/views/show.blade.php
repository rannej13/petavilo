@extends('site::layouts.app')

@section('title', $organisation->getAttribute('name'))
@section('description', \Illuminate\Support\Str::limit((string) $organisation->getAttribute('description'), 155))

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        @unless($organisation->isVerified())
            <div class="notice notice--caution"><p class="notice__title">Only you can see this page</p><p>Status: {{ $organisation->statusLabel() }}. It goes public once we have verified your rescue.</p></div>
        @endunless

        <header class="stack stack--tight">
            <div class="row row--wrap">
                <h1 class="title-page">{{ $organisation->getAttribute('name') }}</h1>
                @if($organisation->isVerified())<span class="badge badge--positive"><x-ui.icon name="shield"/> Verified rescue</span>@endif
            </div>
            <p class="text-meta">{{ $organisation->getAttribute('town') }}, {{ $organisation->getAttribute('country') === 'US' ? 'United States' : 'United Kingdom' }} · {{ implode(', ', $organisation->speciesLabels()) }}</p>
        </header>

        <div class="grid grid--split">
            <div class="stack">
                <div class="prose">{!! nl2br(e((string) $organisation->getAttribute('description'))) !!}</div>
                <ul class="welfare-list">
                    @if(filled($organisation->getAttribute('charity_number')))
                        <li class="welfare-list__item"><span>{{ $organisation->getAttribute('country') === 'US' ? 'EIN' : 'Charity number' }}</span><strong>{{ $organisation->getAttribute('charity_number') }}</strong></li>
                    @endif
                    @if($organisation->getAttribute('accepts_fosters'))
                        <li class="welfare-list__item"><span>Fosterers</span><strong>Looking for fosterers</strong></li>
                    @endif
                    @if($organisation->getAttribute('takes_rehoming_requests'))
                        <li class="welfare-list__item"><span>Rehoming</span><strong>Can help owners rehome</strong></li>
                    @endif
                </ul>
            </div>
            <aside class="card"><div class="card__body stack stack--tight">
                <h2 class="card__title">Contact</h2>
                @if(filled($organisation->getAttribute('website')))
                    <a class="button button--secondary" href="{{ $organisation->getAttribute('website') }}" rel="nofollow noopener" target="_blank">Visit website</a>
                @endif
                <p class="text-muted">To adopt, use Apply on an animal below. Your application goes straight to the rescue.</p>
                @if($organisation->acceptsDonations())
                    <a href="#donate" class="button button--primary">Donate</a>
                @endif
            </div></aside>
        </div>

        @if($organisation->acceptsDonations())
            @include('rescue::partials.donate-box', ['organisation' => $organisation])
        @endif

        <section class="stack">
            <h2 class="title-section">Animals looking for homes</h2>
            @if($listings->isNotEmpty())
                <div class="grid grid--listings">
                    @foreach($listings as $listing)
                        <div class="stack stack--tight">
                            <x-ui.listing-card :listing="$listing"/>
                            <a href="{{ route('rescue.apply', $listing->getKey()) }}" class="button button--secondary button--small">Apply to adopt</a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No animals listed right now.</p>
            @endif
        </section>
    </div>
</div>
@endsection
