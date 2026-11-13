@extends('site::layouts.app')

@section('title', 'Pet food matcher: what should my pet eat?')
@section('description', 'Answer a few quick questions to find the right type of food for your cat, dog, rabbit or guinea pig.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Food matcher</p>
            <h1 class="title-page">What should my pet eat?</h1>
            <p class="text-lead">Quick, sensible guidance. For any health condition, your vet has the final say.</p>
        </header>

        <form method="GET" class="card"><div class="card__body stack">
            @foreach($questions as $key => $question)
                <div class="field">
                    <label class="field__label" for="food-{{ $key }}">{{ $question['label'] }}</label>
                    <select id="food-{{ $key }}" name="{{ $key }}" class="select" @if($key === 'species') required @endif>
                        @if($key !== 'species')<option value="">Not sure</option>@endif
                        @foreach($question['options'] as $value => $label)
                            <option value="{{ $value }}" @selected(($answers[$key] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <button type="submit" class="button button--primary">Show me</button>
        </div></form>

        @if($result)
            <section class="stack" id="result">
                <h2 class="title-section">{{ $result['title'] }}</h2>
                <ul class="stack stack--tight">
                    @foreach($result['points'] as $point)
                        <li class="row row--top"><x-ui.icon name="check"/><span>{{ $point }}</span></li>
                    @endforeach
                </ul>
                <div class="card"><div class="card__body stack stack--tight">
                    <h3 class="title-card">Shopping list</h3>
                    <div class="row row--wrap">
                        @foreach($result['shopping'] as $link)
                            <a class="button button--secondary button--small" href="{{ $link['url'] }}" rel="nofollow sponsored noopener" target="_blank">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                    @if($result['affiliate'])
                        <p class="text-meta">These are affiliate links. We may earn a small commission at no cost to you. <a class="text-link" href="{{ route('pages.show', 'affiliate-disclosure') }}">How we make money</a></p>
                    @endif
                </div></div>
                <p><a class="text-link" href="{{ route('pettools.poison', ['pet' => in_array($result['species'], ['cat', 'dog'], true) ? $result['species'] : 'rabbit', 'type' => 'food']) }}">Foods to avoid</a></p>
            </section>
        @endif
    </div>
</div>
@endsection
