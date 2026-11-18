@extends('site::layouts.app')

@section('title', 'Is this pet right for me?')
@section('description', 'Answer 11 quick questions and see which pets fit your home, hours and budget, with honest reasons and real monthly costs.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')
        <header class="stack stack--tight">
            <p class="text-eyebrow">2-minute quiz</p>
            <h1 class="title-page">Is this pet right for me?</h1>
            <p class="text-lead">Honest answers about which pets fit your home, hours and budget. It's a guide to help you choose, not an approval.</p>
        </header>

        @if($complete)
            @php $top = $results[0]; @endphp
            <section class="card"><div class="card__body stack">
                <p class="text-eyebrow">Your best fit</p>
                <h2 class="title-section">{{ $top['label'] }}</h2>
                @if($top['good'] !== [])<p>{{ implode('. ', $top['good']) }}.</p>@endif
                <div class="row row--wrap">
                    <a href="{{ route('rescue.adopt', ['species' => $top['adopt']]) }}" class="button button--primary">Find {{ lcfirst($top['label']) }} at a rescue near you</a>
                    @auth
                        <form method="POST" action="{{ route('pettools.match.save') }}">
                            @csrf
                            @foreach($answers as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                            <button type="submit" class="button button--secondary">{{ $saved ? 'Update my saved answers' : 'Save answers for rescue applications' }}</button>
                        </form>
                    @else
                        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="button button--secondary">Sign in to save for rescue applications</a>
                    @endauth
                </div>
            </div></section>

            <section class="stack">
                <h2 class="title-section">How each pet fits you</h2>
                <div class="grid grid--three">
                    @foreach($results as $result)
                        <article class="card"><div class="card__body stack stack--tight">
                            <div class="row row--between"><h3 class="title-card">{{ $result['label'] }}</h3><span class="pill {{ in_array($result['fit'], ['Great fit', 'Good fit'], true) ? 'is-active' : '' }}">{{ $result['fit'] }}</span></div>
                            @if($result['good'] !== [])
                                <ul class="stack stack--tight" style="margin: 0; padding-left: 1.1em">@foreach($result['good'] as $line)<li>{{ $line }}</li>@endforeach</ul>
                            @endif
                            @if($result['watch'] !== [])
                                <p class="text-eyebrow">Think about</p>
                                <ul class="stack stack--tight" style="margin: 0; padding-left: 1.1em">@foreach($result['watch'] as $line)<li>{{ $line }}</li>@endforeach</ul>
                            @endif
                            <p class="text-muted" style="font-size: var(--text-sm)">Usually lives {{ $result['lifespan'] }}.</p>
                            @if($result['cost'])<p class="text-muted" style="font-size: var(--text-sm)">{{ $result['cost']['line'] }}</p>@endif
                        </div></article>
                    @endforeach
                </div>
            </section>

            <aside class="notice notice--accent"><p class="notice__title">Before you go further</p>
                <ul style="margin: 0; padding-left: 1.1em">@foreach($checks as $check)<li>{{ $check }}</li>@endforeach</ul>
            </aside>

            <p class="text-muted" style="font-size: var(--text-xs)">Costs: @foreach($sources as $source)<a class="text-link" href="{{ $source['url'] }}" rel="noopener" target="_blank">{{ $source['label'] }}</a>@if(! $loop->last) · @endif @endforeach. Lifespans are typical ranges; individual animals vary. <a class="text-link" href="{{ route('pettools.match', ['new' => 1]) }}">Start again</a></p>
        @else
            <form method="GET" action="{{ route('pettools.match') }}" class="card"><div class="card__body stack">
                @foreach($questions as $key => $question)
                    <fieldset class="field" style="border: 0; padding: 0; margin: 0">
                        <legend class="field__label">{{ $loop->iteration }}. {{ $question['label'] }}</legend>
                        <div class="row row--wrap">
                            @foreach($question['options'] as $option)
                                <label class="pill" style="cursor: pointer"><input type="radio" name="{{ $key }}" value="{{ $option }}" required @checked(($answers[$key] ?? null) === $option)> {{ $option }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                <div class="row row--wrap"><button type="submit" class="button button--primary">See my results</button></div>
                <p class="field__hint">Nothing is saved unless you choose to save it.</p>
            </div></form>
        @endif
    </div>
</div>
@endsection
