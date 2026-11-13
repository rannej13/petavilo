@extends('site::layouts.app')

@section('title', $organisation ? 'Edit your rescue' : 'Add your rescue')

@section('content')
<div class="shell shell--form page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow">For rescues and charities</p>
            <h1 class="title-page">{{ $organisation ? 'Edit your rescue' : 'Add your rescue, free' }}</h1>
            <p class="text-lead">List animals for adoption, take applications online, find fosterers and get a Verified rescue badge on your adverts. We check your charity or nonprofit details before the badge shows.</p>
        </header>

        @if($organisation)
            <div class="notice"><p class="notice__title">Status: {{ $organisation->statusLabel() }}</p></div>
        @endif

        <form method="POST" action="{{ route('rescue.join.store') }}" class="card"><div class="card__body stack">
            @csrf
            {{ \App\Support\SpamGuard::fields() }}

            <div class="field">
                <label class="field__label" for="name">Rescue or charity name</label>
                <input id="name" name="name" class="input" required maxlength="120" value="{{ old('name', $organisation?->getAttribute('name')) }}">
                @error('name')<p class="field__error">{{ $message }}</p>@enderror
            </div>

            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label" for="country">Country</label>
                    <select id="country" name="country" class="select">
                        @foreach(['GB' => 'United Kingdom', 'US' => 'United States'] as $code => $label)
                            <option value="{{ $code }}" @selected(old('country', $organisation?->getAttribute('country') ?? \Modules\CatMarket\Support\CatMarketSettings::visitorCountry()) === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="town">Town or city</label>
                    <input id="town" name="town" class="input" required maxlength="80" value="{{ old('town', $organisation?->getAttribute('town')) }}">
                    @error('town')<p class="field__error">{{ $message }}</p>@enderror
                </div>
            </div>

            <fieldset class="field">
                <legend class="field__label">Animals you care for</legend>
                <div class="row row--wrap">
                    @foreach($speciesOptions as $key => $label)
                        <label class="row"><input type="checkbox" name="species[]" value="{{ $key }}" @checked(in_array($key, (array) old('species', $organisation?->getAttribute('species') ?? []), true))> <span>{{ $label }}</span></label>
                    @endforeach
                </div>
                @error('species')<p class="field__error">{{ $message }}</p>@enderror
            </fieldset>

            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label" for="charity_number">Charity number or US EIN</label>
                    <input id="charity_number" name="charity_number" class="input" maxlength="40" value="{{ old('charity_number', $organisation?->getAttribute('charity_number')) }}">
                    <p class="field__hint">We check this with the Charity Commission, OSCR, the NI Charity Commission or the IRS.</p>
                </div>
                <div class="field">
                    <label class="field__label" for="website">Website</label>
                    <input id="website" name="website" type="url" class="input" maxlength="200" placeholder="https://" value="{{ old('website', $organisation?->getAttribute('website')) }}">
                    @error('website')<p class="field__error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="contact_email">Email we can reach you on</label>
                <input id="contact_email" name="contact_email" type="email" class="input" required maxlength="160" value="{{ old('contact_email', $organisation?->getAttribute('contact_email') ?? auth()->user()->email) }}">
                <p class="field__hint">Not shown publicly. Adopters contact you through the site.</p>
                @error('contact_email')<p class="field__error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label class="field__label" for="description">About your rescue</label>
                <textarea id="description" name="description" class="textarea" rows="6" required minlength="40" maxlength="3000">{{ old('description', $organisation?->getAttribute('description')) }}</textarea>
                <p class="field__hint">What you do, the area you cover and how adoption works with you.</p>
                @error('description')<p class="field__error">{{ $message }}</p>@enderror
            </div>

            <label class="row"><input type="checkbox" name="accepts_fosters" value="1" @checked(old('accepts_fosters', $organisation?->getAttribute('accepts_fosters')))> <span>We are looking for fosterers</span></label>
            <label class="row"><input type="checkbox" name="takes_rehoming_requests" value="1" @checked(old('takes_rehoming_requests', $organisation?->getAttribute('takes_rehoming_requests')))> <span>We can help owners who need to rehome a pet</span></label>

            <button type="submit" class="button button--primary">{{ $organisation ? 'Save changes' : 'Send for checks' }}</button>
        </div></form>
    </div>
</div>
@endsection
