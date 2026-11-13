@extends('panel::layouts.panel', ['panelSection' => 'adoptions'])

@section('title', 'My adoption applications')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">My adoption applications</h1>
    </div>
    <a href="{{ route('rescue.adopt') }}" class="button button--ghost">Browse pets to adopt</a>
</header>

@if($applications->isEmpty())
    <x-ui.empty-state icon="heart" title="No applications yet" text="Find a pet from a verified rescue and press Apply to adopt.">
        <a href="{{ route('rescue.adopt') }}" class="button button--primary">Adopt a pet</a>
    </x-ui.empty-state>
@else
    <section class="card"><div class="data-list">
        @foreach($applications as $application)
            @php $listing = $listingTitles[(int) $application->getAttribute('listing_id')] ?? null; @endphp
            <article class="data-row">
                <div class="data-row__main stack stack--tight">
                    <p class="data-row__title">
                        @if($listing)<a href="{{ route('listings.show', $listing['slug']) }}">{{ $listing['title'] }}</a>@else #{{ $application->getAttribute('listing_id') }} @endif
                    </p>
                    <div class="data-row__meta">
                        <span class="badge">{{ $application->statusLabel() }}</span>
                        <span>{{ $application->organisation?->name }}</span>
                        <span>{{ $application->getAttribute('updated_at')?->diffForHumans() }}</span>
                    </div>
                    @if($note = $application->latestNote())
                        <p class="text-muted">Message from the rescue: {{ $note }}</p>
                    @endif
                </div>
                @if(in_array($application->getAttribute('status'), ['submitted', 'reviewing', 'more_info'], true))
                    <form method="POST" action="{{ route('rescue.panel.withdraw', $application->getKey()) }}" class="data-row__actions">
                        @csrf
                        <button type="submit" class="button button--ghost button--small">Withdraw</button>
                    </form>
                @endif
            </article>
        @endforeach
    </div></section>
@endif
@endsection
