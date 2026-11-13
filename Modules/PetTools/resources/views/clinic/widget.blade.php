<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $clinic->getAttribute('name') }}: pet triage</title>
    @vite(['resources/ts/app.ts'])
</head>
<body>
<main class="shell shell--narrow page">
    <div class="stack">
        <header class="stack stack--tight">
            <p class="text-eyebrow">{{ $clinic->getAttribute('name') }}</p>
            <h1 class="title-section">Is my pet's problem urgent?</h1>
            <p class="text-muted">Answer a few questions. We will tell you how quickly to be seen and send your answers to the practice so they are ready for you.</p>
        </header>

        @if(! $clinic->isLive())
            <div class="notice"><p>This form is not available right now. Please phone the practice{{ $clinic->getAttribute('phone') ? ' on '.$clinic->getAttribute('phone') : '' }}.</p></div>
        @elseif($assessment)
            <section class="notice {{ $assessment['tier'] === 'red' ? 'notice--caution' : ($assessment['tier'] === 'amber' ? 'notice--accent' : '') }}" role="status">
                @if($assessment['tier'] === 'red')
                    <p class="notice__title">Emergency: get help now</p>
                    <p>Call {{ $clinic->getAttribute('phone') ? $clinic->getAttribute('name').' on '.$clinic->getAttribute('phone') : 'the practice' }} now and tell them you are on your way.</p>
                    @if($clinic->getAttribute('emergency_text'))<p><strong>{{ $clinic->getAttribute('emergency_text') }}</strong></p>@endif
                @elseif($assessment['tier'] === 'amber')
                    <p class="notice__title">Your pet should be seen today</p>
                    <p>Please book a same-day appointment. Call if anything gets worse.</p>
                @else
                    <p class="notice__title">Not urgent from your answers</p>
                    <p>Book a routine appointment, and call if anything changes or you are worried.</p>
                @endif
            </section>
            @if($assessment['tier'] !== 'red' && $clinic->getAttribute('booking_url'))
                <a href="{{ $clinic->getAttribute('booking_url') }}" target="_top" class="button button--primary">Book an appointment</a>
            @endif
            <p class="text-meta">Your answers have been sent to {{ $clinic->getAttribute('name') }}.</p>
        @else
            <form method="POST" action="{{ route('pettools.widget.submit', $clinic->getAttribute('token')) }}" class="stack">
                {{ \App\Support\SpamGuard::fields() }}
                @if($errors->any())<div class="notice notice--caution"><p>{{ $errors->first() }}</p></div>@endif
                <div class="field__row field__row--two">
                    <div class="field"><label class="field__label" for="w-pet">Pet's name</label><input id="w-pet" name="pet_name" class="input" maxlength="60" value="{{ old('pet_name') }}"></div>
                    <div class="field"><label class="field__label" for="w-age">Age</label><input id="w-age" name="age" class="input" maxlength="30" placeholder="e.g. 4 years" value="{{ old('age') }}"></div>
                </div>
                @foreach($questions as $key => $question)
                    <div class="field">
                        <label class="field__label" for="w-{{ $key }}">{{ $question['label'] }}</label>
                        <select id="w-{{ $key }}" name="{{ $key }}" class="select" required>
                            <option value="">Choose</option>
                            @foreach($question['options'] as $value => $label)<option value="{{ $value }}" @selected(old($key) === (string) $value)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                @endforeach
                <details class="card"><summary class="card__body text-link">Cats: score their face (optional)</summary><div class="card__body stack">
                    @foreach($markers as $key => $marker)
                        <div class="field"><label class="field__label" for="w-fgs-{{ $key }}">{{ $marker['label'] }}</label>
                            <select id="w-fgs-{{ $key }}" name="fgs_{{ $key }}" class="select"><option value="">Can't tell</option>@foreach($marker['options'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    @endforeach
                </div></details>
                <div class="field"><label class="field__label" for="w-notes">Anything else</label><textarea id="w-notes" name="notes" class="textarea" rows="3" maxlength="1000">{{ old('notes') }}</textarea></div>
                <div class="field"><label class="field__label" for="w-name">Your name</label><input id="w-name" name="owner_name" class="input" required maxlength="80" value="{{ old('owner_name') }}"></div>
                <div class="field__row field__row--two">
                    <div class="field"><label class="field__label" for="w-phone">Phone</label><input id="w-phone" name="owner_phone" type="tel" class="input" maxlength="40" value="{{ old('owner_phone') }}"></div>
                    <div class="field"><label class="field__label" for="w-email">Email</label><input id="w-email" name="owner_email" type="email" class="input" maxlength="160" value="{{ old('owner_email') }}"></div>
                </div>
                <label class="row row--top"><input type="checkbox" name="consent" value="1" required> <span>Send my answers and contact details to {{ $clinic->getAttribute('name') }}.</span></label>
                <button type="submit" class="button button--primary button--large">Check urgency</button>
                <p class="text-meta">If your pet is collapsed, struggling to breathe, bleeding heavily or can't wee, don't fill this in: call {{ $clinic->getAttribute('phone') ?: 'the practice' }} now. This form gives general guidance, not a diagnosis.</p>
            </form>
        @endif
        <p class="text-meta">Powered by {{ config('catmarket.site_name') }}</p>
    </div>
</main>
</body>
</html>
