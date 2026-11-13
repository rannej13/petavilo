@extends('site::layouts.app')

@section('title', 'Would you help a hungry or injured animal?')
@section('description', 'Answer our quick question, see how others answered, and learn simple, safe ways to help an animal in need.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <section class="welfare-poll stack" aria-labelledby="poll-question">
            <h1 id="poll-question" class="title-page">{{ $campaign['question'] }}</h1>

            @if($answered === null)
                <form method="POST" action="{{ route('rescue.welfare.answer', $slug) }}" class="stack stack--tight">
                    @csrf
                    @foreach($campaign['answers'] as $key => $label)
                        <button type="submit" name="answer" value="{{ $key }}" class="button button--secondary button--block">{{ $label }}</button>
                    @endforeach
                </form>
                <p class="text-meta">Anonymous. Answering does not report an animal or ask anyone to come out.</p>
            @else
                <div id="results" class="stack stack--tight">
                    <p class="text-muted">Thanks. Here is how {{ number_format($total) }} {{ \Illuminate\Support\Str::plural('person', $total) }} answered:</p>
                    @foreach($results as $row)
                        <div class="stack stack--tight">
                            <div class="row row--between"><span>{{ $row['label'] }}@if($row['key'] === $answered) <strong>(you)</strong>@endif</span><strong>{{ $row['percent'] }}%</strong></div>
                            <div style="height:8px;border-radius:4px;background:var(--surface-sunken, #eee);overflow:hidden"><div style="height:100%;width:{{ $row['percent'] }}%;background:var(--accent, #7c3aed)"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="stack">
            <h2 class="title-section">Simple, safe ways to help</h2>
            @foreach($campaign['actions'] as $action)
                <div class="card"><div class="card__body stack stack--tight">
                    <h3 class="title-card">{{ $action['title'] }}</h3>
                    <p class="text-muted">{{ $action['text'] }}</p>
                </div></div>
            @endforeach
        </section>

        <div class="row row--wrap">
            <a href="{{ route('rescue.found-animal') }}" class="button button--primary">What to do if you find an animal</a>
            <a href="{{ route('rescue.foster') }}" class="button button--ghost">Foster or volunteer</a>
        </div>
    </div>
</div>
@endsection
