@extends('site::layouts.app')

@section('title', 'Apply to adopt: '.$listing->getAttribute('title'))

@section('content')
<div class="shell shell--form page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow">Apply to adopt</p>
            <h1 class="title-page">{{ $listing->getAttribute('title') }}</h1>
            <p class="text-lead">Your application goes to <a class="text-link" href="{{ route('rescue.show', $organisation->getAttribute('slug')) }}">{{ $organisation->getAttribute('name') }}</a>. Only they and you can see it.</p>
        </header>

        <form method="POST" action="{{ route('rescue.apply.store', $listing->getKey()) }}" class="card"><div class="card__body stack">
            @csrf
            {{ \App\Support\SpamGuard::fields() }}

            @foreach($questions as $key => $question)
                <div class="field">
                    <label class="field__label" for="answer-{{ $key }}">{{ $question['label'] }}</label>
                    <select id="answer-{{ $key }}" name="answers[{{ $key }}]" class="select" required>
                        <option value="">Choose</option>
                        @foreach($question['options'] as $option)
                            <option value="{{ $option }}" @selected(old('answers.'.$key) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('answers.'.$key)<p class="field__error">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <div class="field">
                <label class="field__label" for="message">Tell the rescue about you</label>
                <textarea id="message" name="message" class="textarea" rows="6" required minlength="30" maxlength="3000" placeholder="Your daily routine, why this pet would suit you, and anything they should know.">{{ old('message') }}</textarea>
                @error('message')<p class="field__error">{{ $message }}</p>@enderror
            </div>

            <p class="text-meta">Do not include bank details. A genuine rescue will never ask for payment before a home check.</p>
            <button type="submit" class="button button--primary">Send application</button>
        </div></form>
    </div>
</div>
@endsection
