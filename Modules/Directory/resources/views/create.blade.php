@extends('site::layouts.app')

@section('title', __('directory::messages.add_business'))

@section('content')
<div class="shell shell--form page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <h1 class="title-page">{{ __('directory::messages.add_business') }}</h1>
            <p class="text-lead">{{ __('directory::messages.add_business_lead') }}</p>
        </header>

        <form method="POST" action="{{ route('directory.store') }}" class="card">
            @csrf
            <div class="card__body stack">
                <div class="field">
                    <label class="field__label" for="b-name">{{ __('directory::messages.name') }}</label>
                    <input id="b-name" name="name" class="input" required maxlength="120" value="{{ old('name') }}">
                </div>
                <div class="field__row field__row--two">
                    <div class="field">
                        <label class="field__label" for="b-type">{{ __('directory::messages.type') }}</label>
                        <select id="b-type" name="type" class="select" required>
                            @foreach($types as $slug => $name)
                                <option value="{{ $slug }}" @selected(old('type') === $slug)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="field__label" for="b-country">Country</label>
                        <select id="b-country" name="country" class="select" required>
                            @foreach($countries as $code => $name)
                                <option value="{{ $code }}" @selected(old('country', $region) === $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field__row field__row--two">
                    <div class="field">
                        <label class="field__label" for="b-town">{{ __('directory::messages.town') }}</label>
                        <input id="b-town" name="town" class="input" required maxlength="80" value="{{ old('town') }}">
                    </div>
                </div>
                <div class="field__row field__row--two">
                    <div class="field">
                        <label class="field__label" for="b-address">{{ __('directory::messages.address') }}</label>
                        <input id="b-address" name="address_line" class="input" maxlength="160" value="{{ old('address_line') }}">
                    </div>
                    <div class="field">
                        <label class="field__label" for="b-postcode">{{ __('directory::messages.postcode') }}</label>
                        <input id="b-postcode" name="postcode" class="input" maxlength="12" value="{{ old('postcode') }}">
                    </div>
                </div>
                <div class="field__row field__row--two">
                    <div class="field">
                        <label class="field__label" for="b-phone">{{ __('directory::messages.phone') }}</label>
                        <input id="b-phone" name="phone" type="tel" class="input" maxlength="40" value="{{ old('phone') }}">
                    </div>
                    <div class="field">
                        <label class="field__label" for="b-email">{{ __('directory::messages.email') }}</label>
                        <input id="b-email" name="email" type="email" class="input" maxlength="160" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="field">
                    <label class="field__label" for="b-website">{{ __('directory::messages.website') }}</label>
                    <input id="b-website" name="website" class="input" maxlength="200" value="{{ old('website') }}">
                </div>
                <div class="field">
                    <label class="field__label" for="b-licence">{{ __('directory::messages.licence_number') }}</label>
                    <input id="b-licence" name="licence_number" class="input" maxlength="80" value="{{ old('licence_number') }}">
                </div>
                <div class="field">
                    <label class="field__label" for="b-description">{{ __('directory::messages.description') }}</label>
                    <textarea id="b-description" name="description" class="textarea" rows="4" maxlength="300">{{ old('description') }}</textarea>
                    <p class="field__hint">{{ __('directory::messages.description_free_hint') }}</p>
                </div>
                <button type="submit" class="button button--primary">{{ __('directory::messages.add_business') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
