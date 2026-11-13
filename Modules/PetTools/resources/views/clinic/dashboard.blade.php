@extends('panel::layouts.panel', ['panelSection' => 'clinic'])

@section('title', 'Clinic triage widget')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">Clinic triage widget</h1>
        @if($clinic)
            <p class="text-muted">
                @if($clinic->isSubscribed()) Subscribed
                @elseif($clinic->onTrial()) Free trial until {{ $clinic->getAttribute('trial_ends_at')->isoFormat('D MMM YYYY') }}
                @else Trial ended. Subscribe to keep the widget working.
                @endif
            </p>
        @endif
    </div>
    <a href="{{ route('pettools.clinic.landing') }}" class="button button--ghost">About the widget</a>
</header>

@if($clinic && ! $clinic->isSubscribed())
    <div class="notice notice--accent">
        <p class="notice__title">{{ $clinic->onTrial() ? 'Enjoying the trial?' : 'Your widget is paused' }}</p>
        <p>Subscribe to the Clinic triage widget plan to keep it running after your trial.</p>
        <a href="{{ route('billing.membership.index') }}" class="button button--primary button--small">See plan and subscribe</a>
    </div>
@endif

<form method="POST" action="{{ route('pettools.clinic.save') }}" class="card"><div class="card__body stack">
    @csrf
    <h2 class="card__title">{{ $clinic ? 'Practice details' : 'Set up your widget' }}</h2>
    <div class="field__row field__row--two">
        <div class="field"><label class="field__label" for="clinic-name">Practice name</label><input id="clinic-name" name="name" class="input" required maxlength="120" value="{{ old('name', $clinic?->getAttribute('name')) }}"></div>
        <div class="field"><label class="field__label" for="clinic-country">Country</label><select id="clinic-country" name="country" class="select">@foreach(['GB' => 'United Kingdom', 'US' => 'United States'] as $code => $label)<option value="{{ $code }}" @selected(old('country', $clinic?->getAttribute('country') ?? 'GB') === $code)>{{ $label }}</option>@endforeach</select></div>
    </div>
    <div class="field__row field__row--two">
        <div class="field"><label class="field__label" for="clinic-email">Send triage notes to</label><input id="clinic-email" name="email" type="email" class="input" required maxlength="160" value="{{ old('email', $clinic?->getAttribute('email') ?? auth()->user()->email) }}">@error('email')<p class="field__error">{{ $message }}</p>@enderror</div>
        <div class="field"><label class="field__label" for="clinic-phone">Practice phone</label><input id="clinic-phone" name="phone" type="tel" class="input" maxlength="40" value="{{ old('phone', $clinic?->getAttribute('phone')) }}"></div>
    </div>
    <div class="field"><label class="field__label" for="clinic-booking">Online booking link (optional)</label><input id="clinic-booking" name="booking_url" type="url" class="input" maxlength="200" placeholder="https://" value="{{ old('booking_url', $clinic?->getAttribute('booking_url')) }}">@error('booking_url')<p class="field__error">{{ $message }}</p>@enderror</div>
    <div class="field"><label class="field__label" for="clinic-emergency">Emergency instructions for red results</label><input id="clinic-emergency" name="emergency_text" class="input" maxlength="300" placeholder="e.g. Out of hours, call Vets Now Leeds on 0113 000 0000" value="{{ old('emergency_text', $clinic?->getAttribute('emergency_text')) }}"></div>
    <button type="submit" class="button button--primary">{{ $clinic ? 'Save' : 'Start 14-day free trial' }}</button>
</div></form>

@if($clinic)
    <section class="card"><div class="card__body stack stack--tight">
        <h2 class="card__title">Embed code</h2>
        <p class="text-muted">Paste this into your website where you want the form to appear.</p>
        <textarea class="textarea" rows="3" readonly onclick="this.select()">{{ $clinic->embedCode() }}</textarea>
        <a href="{{ route('pettools.widget', $clinic->getAttribute('token')) }}" target="_blank" class="text-link">Preview the form</a>
    </div></section>

    <section class="card">
        <div class="card__head"><h2 class="card__title">Recent triages</h2></div>
        @if($submissions->isEmpty())
            <div class="card__body"><p class="text-muted">None yet.</p></div>
        @else
            <div class="data-list">
                @foreach($submissions as $submission)
                    <article class="data-row"><div class="data-row__main stack stack--tight">
                        <div class="data-row__meta">
                            <span class="badge {{ $submission->tier === 'red' ? 'badge--critical' : ($submission->tier === 'amber' ? 'badge--caution' : 'badge--positive') }}">{{ strtoupper($submission->tier) }}</span>
                            <strong>{{ $submission->pet_name ?: 'Pet' }}</strong>
                            <span>{{ $submission->owner_name }} · {{ $submission->owner_phone }} {{ $submission->owner_email }}</span>
                            <span>{{ $submission->created_at?->diffForHumans() }}</span>
                        </div>
                        <details><summary class="text-link">SOAP note</summary><pre style="white-space:pre-wrap;font:inherit">{{ $submission->soap }}</pre></details>
                    </div></article>
                @endforeach
            </div>
        @endif
    </section>
@endif
@endsection
