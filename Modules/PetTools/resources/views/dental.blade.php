@extends('site::layouts.app')

@section('title', 'Cat dental and senior health check')
@section('description', 'Check your cat\'s teeth and gums against four stages of dental disease, and spot early signs of kidney problems in older cats.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Dental &amp; senior check</p>
            <h1 class="title-page">Teeth, gums and senior health</h1>
            <p class="text-lead">Most cats over three have some dental disease, and kidney disease is common in older cats. Both are easier to treat early.</p>
        </header>

        @if($result)
            <section id="result" class="stack">
                <div class="card"><div class="card__body stack stack--tight">
                    <p class="text-eyebrow">Estimated dental stage</p>
                    <p class="title-page">Stage {{ $result['stage'] }} of 4</p>
                    <p>{{ $result['stage_text'] }}</p>
                    @if($result['stage'] >= 3)
                        <p class="text-muted">Bacteria from infected gums can enter the bloodstream and are linked with kidney and heart problems, so treating dental disease protects more than the mouth.</p>
                    @endif
                </div></div>
                @if($result['senior_text'])
                    <div class="notice {{ $result['senior_urgent'] ? 'notice--caution' : '' }}"><p class="notice__title">Senior health</p><p>{{ $result['senior_text'] }}</p></div>
                @endif

                @if($result['dental_check'] || $result['senior_check'])
                    <form method="POST" action="{{ route('pettools.lead') }}" class="card"><div class="card__body stack">
                        @csrf
                        {{ \App\Support\SpamGuard::fields() }}
                        <h2 class="card__title">Ask a local vet to contact you</h2>
                        <p class="text-muted">Free and no obligation. We pass your details to a vet practice near you so they can offer an appointment and a written estimate.</p>
                        <input type="hidden" name="kind" value="{{ $result['dental_check'] ? 'dental' : 'senior' }}">
                        <input type="hidden" name="summary" value="Dental stage {{ $result['stage'] }}{{ $result['senior_check'] ? ', senior check'.($result['senior_urgent'] ? ' (several warning signs)' : '') : '' }}">
                        <div class="field__row field__row--two">
                            <div class="field"><label class="field__label" for="lead-name">Your name</label><input id="lead-name" name="name" class="input" required maxlength="80" value="{{ old('name', auth()->user()?->name) }}"></div>
                            <div class="field"><label class="field__label" for="lead-pet">Pet's name</label><input id="lead-pet" name="pet_name" class="input" maxlength="60" value="{{ old('pet_name') }}"></div>
                        </div>
                        <div class="field__row field__row--two">
                            <div class="field"><label class="field__label" for="lead-email">Email</label><input id="lead-email" name="email" type="email" class="input" required maxlength="160" value="{{ old('email', auth()->user()?->email) }}">@error('email')<p class="field__error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label class="field__label" for="lead-phone">Phone (optional)</label><input id="lead-phone" name="phone" type="tel" class="input" maxlength="40" value="{{ old('phone') }}"></div>
                        </div>
                        <div class="field"><label class="field__label" for="lead-town">Town or postcode area</label><input id="lead-town" name="town" class="input" required maxlength="80" value="{{ old('town') }}"></div>
                        <label class="row row--top"><input type="checkbox" name="consent" value="1" required> <span>I agree to {{ config('catmarket.site_name') }} sharing these details with one or more vet practices near me so they can contact me about an appointment. <a class="text-link" href="{{ route('pages.show', 'privacy') }}">Privacy policy</a></span></label>
                        @error('consent')<p class="field__error">{{ $message }}</p>@enderror
                        <button type="submit" class="button button--primary">Send my request</button>
                    </div></form>
                @endif
            </section>
            <hr>
        @endif

        <form method="GET" action="{{ route('pettools.dental') }}#result" class="card"><div class="card__body stack">
            <fieldset class="field">
                <legend class="field__label">Lift your cat's lip gently. Which looks closest?</legend>
                @foreach($gumStages as $value => $label)
                    <label class="row row--top"><input type="radio" name="gums" value="{{ $value }}" required @checked((int) ($input['gums'] ?? 0) === $value)> <span>{{ $label }}</span></label>
                @endforeach
            </fieldset>
            <fieldset class="field">
                <legend class="field__label">Any of these?</legend>
                @foreach($signs as $key => $label)
                    <label class="row"><input type="checkbox" name="signs[]" value="{{ $key }}" @checked(in_array($key, (array) ($input['signs'] ?? []), true))> <span>{{ $label }}</span></label>
                @endforeach
            </fieldset>
            <div class="field">
                <label class="field__label" for="dental-age">Age</label>
                <select id="dental-age" name="age" class="select">
                    @foreach(['' => 'Choose', 'under-10' => 'Under 10', '10-14' => '10 to 14', '15+' => '15 or older'] as $value => $label)<option value="{{ $value }}" @selected(($input['age'] ?? '') === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <fieldset class="field">
                <legend class="field__label">For older cats: any changes?</legend>
                @foreach($seniorSigns as $key => $label)
                    <label class="row"><input type="checkbox" name="senior[]" value="{{ $key }}" @checked(in_array($key, (array) ($input['senior'] ?? []), true))> <span>{{ $label }}</span></label>
                @endforeach
            </fieldset>
            <button type="submit" class="button button--primary">Check</button>
        </div></form>

        <p class="text-meta">A guide only. Only a vet can grade dental disease properly, usually with X-rays under anaesthetic.</p>
    </div>
</div>
@endsection
