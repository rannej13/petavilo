@extends('site::layouts.app')

@section('title', 'Why is my cat weeing outside the litter tray? Litter Box Forensics')
@section('description', 'Answer ten quick questions to find out why your cat is going outside the litter tray, with a step-by-step fix list.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Litter Box Forensics</p>
            <h1 class="title-page">Why is my cat going outside the tray?</h1>
            <p class="text-lead">Ten quick questions. We check the things vets and behaviourists look at first.</p>
        </header>

        @if($result)
            <section id="result" class="stack">
                @if($result['emergency'])
                    <div class="notice notice--caution" role="alert">
                        <p class="notice__title">Call a vet now</p>
                        <p>Straining, crying, tiny frequent wees or blood can mean a bladder problem. In male cats it can be a blockage, which is an emergency. Do not wait to see if it settles.</p>
                        <a href="{{ route('directory.type', ['type' => 'vets']) }}" class="button button--primary">Find a vet near me</a>
                    </div>
                @elseif($result['findings'] === [])
                    <div class="notice"><p class="notice__title">Your set-up looks good</p><p>Nothing stands out. A vet check is the next step, as hidden pain or cystitis is a common cause.</p></div>
                @else
                    <h2 class="title-section">What to change</h2>
                    @foreach($result['findings'] as $index => $finding)
                        <div class="card"><div class="card__body stack stack--tight">
                            <h3 class="title-card">{{ $index + 1 }}. {{ $finding['title'] }}</h3>
                            <p class="text-muted">{{ $finding['text'] }}</p>
                        </div></div>
                    @endforeach
                    <p class="text-muted">Clean accident spots with an enzyme cleaner, not bleach or ammonia, or the smell will draw your cat back. If nothing improves in two weeks, see your vet.</p>
                    @if($shopping !== [])
                        <div class="card"><div class="card__body stack stack--tight">
                            <h3 class="title-card">Things that help</h3>
                            <div class="row row--wrap">
                                @foreach($shopping as $link)
                                    <a class="button button--secondary button--small" href="{{ $link['url'] }}" rel="nofollow sponsored noopener" target="_blank">{{ $link['label'] }}</a>
                                @endforeach
                            </div>
                            @if($affiliate)<p class="text-meta">Affiliate links. We may earn a small commission at no cost to you.</p>@endif
                        </div></div>
                    @endif
                @endif
            </section>
            <hr>
        @endif

        <form method="GET" action="{{ route('pettools.litter') }}#result" class="card"><div class="card__body stack">
            @foreach($questions as $key => $question)
                <div class="field">
                    <label class="field__label" for="litter-{{ $key }}">{{ $question['label'] }}</label>
                    <select id="litter-{{ $key }}" name="{{ $key }}" class="select" required>
                        @foreach($question['options'] as $value => $label)
                            <option value="{{ $value }}" @selected(($answers[$key] ?? null) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <button type="submit" class="button button--primary">Find the cause</button>
        </div></form>

        <p class="text-meta">General guidance only. Toileting problems can be a sign of illness, so involve your vet.</p>
    </div>
</div>
@endsection
