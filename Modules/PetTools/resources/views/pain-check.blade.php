@extends('site::layouts.app')

@section('title', 'Is my cat in pain? Free cat pain and emergency check')
@section('description', 'Score your cat\'s face with the Feline Grimace Scale and check for emergency signs such as a blocked bladder. Free, takes two minutes.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Cat pain check</p>
            <h1 class="title-page">Is my cat in pain?</h1>
            <p class="text-lead">Cats hide pain well. Their face often gives it away. Look at your cat for a minute while they are awake and calm, then answer below.</p>
        </header>

        @if($result)
            <section id="result" class="notice {{ $result['tier'] === 'red' ? 'notice--caution' : ($result['tier'] === 'amber' ? 'notice--accent' : '') }}" role="status">
                <p class="notice__title">{{ $result['title'] }}</p>
                <p>{{ $result['advice'] }}</p>
                @if($result['scored'] > 0)
                    <p class="text-meta">Face score: {{ $result['score'] }} out of {{ $result['scored'] * 2 }}.</p>
                @endif
                @foreach(array_merge($result['red_flags'], $result['concerns']) as $item)
                    <p>• {{ $item }}</p>
                @endforeach
            </section>

            @if($result['tier'] === 'red')
                <div class="row row--wrap">
                    <a href="{{ route('directory.type', ['type' => 'vets']) }}" class="button button--primary">Find a vet near me</a>
                    <a href="{{ route('rescue.found-animal') }}" class="button button--ghost">Emergency numbers</a>
                    <a href="{{ route('pettools.poison') }}" class="button button--ghost">Poison checker</a>
                </div>
            @else
                @include('pettools::partials.televet')
                <div class="row row--wrap">
                    <a href="{{ route('directory.type', ['type' => 'vets']) }}" class="button button--secondary">Find a vet near me</a>
                    @if(\Illuminate\Support\Facades\Route::has('pettools.insurance'))
                        <a href="{{ route('pettools.insurance') }}" class="button button--ghost">Vet bills: insurance or savings?</a>
                    @endif
                </div>
            @endif
            <hr>
        @endif

        <form method="GET" action="{{ route('pettools.pain') }}#result" class="stack">
            <input type="hidden" name="done" value="1">

            <section class="card"><div class="card__body stack">
                <h2 class="card__title">First: any of these right now?</h2>
                @foreach($redFlags as $key => $label)
                    <label class="row row--top"><input type="checkbox" name="flags[]" value="{{ $key }}" @checked(in_array($key, (array) ($input['flags'] ?? []), true))> <span>{{ $label }}</span></label>
                @endforeach
            </div></section>

            <section class="card"><div class="card__body stack">
                <h2 class="card__title">Your cat's face</h2>
                <p class="text-muted">Based on the Feline Grimace Scale, a pain-scoring tool developed by vets. Pick the closest match, or skip any you can't see.</p>
                @foreach($markers as $key => $marker)
                    <fieldset class="field">
                        <legend class="field__label">{{ $marker['label'] }}</legend>
                        <div class="stack stack--tight">
                            @foreach($marker['options'] as $value => $option)
                                <label class="row"><input type="radio" name="{{ $key }}" value="{{ $value }}" @checked(isset($input[$key]) && (string) $input[$key] === (string) $value)> <span>{{ $option }}</span></label>
                            @endforeach
                            <label class="row"><input type="radio" name="{{ $key }}" value="" @checked(! isset($input[$key]) || $input[$key] === '')> <span class="text-muted">Can't tell</span></label>
                        </div>
                    </fieldset>
                @endforeach
            </div></section>

            <section class="card"><div class="card__body stack">
                <h2 class="card__title">Anything else?</h2>
                @foreach($concerns as $key => $label)
                    <label class="row row--top"><input type="checkbox" name="concerns[]" value="{{ $key }}" @checked(in_array($key, (array) ($input['concerns'] ?? []), true))> <span>{{ $label }}</span></label>
                @endforeach
            </div></section>

            <button type="submit" class="button button--primary button--large">Check</button>
        </form>

        <p class="text-meta">This check helps you decide how quickly to see a vet. It cannot diagnose anything. If you are worried, call your vet whatever the result. The Feline Grimace Scale was developed by researchers at the University of Montreal; scores of 4 or more out of 10 suggest a cat may need pain relief.</p>
    </div>
</div>
@endsection
