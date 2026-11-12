@extends('panel::layouts.panel', ['panelSection' => 'business'])

@section('title', __('directory::messages.my_business'))

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">{{ __('directory::messages.my_business') }}</h1>
    </div>
    <a href="{{ route('directory.create') }}" class="button button--ghost">{{ __('directory::messages.add_business') }}</a>
</header>

@if(session('success'))<div class="notice notice--accent"><p>{{ session('success') }}</p></div>@endif

@forelse($businesses as $business)
    @php $paid = $business->isPaid(); @endphp
    <section class="card">
        <div class="card__head">
            <h2 class="card__title">{{ $business->getAttribute('name') }}</h2>
            <span class="badge {{ $business->getAttribute('is_published') ? 'badge--positive' : 'badge--caution' }}">
                {{ $business->getAttribute('is_published') ? __('directory::messages.live') : __('directory::messages.awaiting_review') }}
            </span>
            <span class="badge">{{ __('directory::messages.tier') }}: {{ ucfirst((string) $business->getAttribute('tier')) }}</span>
        </div>
        <div class="card__body stack stack--tight">
            <p class="text-meta">{{ __('directory::messages.completeness', ['percent' => $business->profileCompleteness()]) }} · {{ __('directory::messages.stats_line', ['views' => number_format((int) $business->getAttribute('view_count')), 'clicks' => number_format((int) $business->getAttribute('website_clicks'))]) }}</p>
        </div>
        <form method="POST" action="{{ route('directory.panel.update', $business) }}" class="card__body stack" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.phone') }}</label>
                    <input name="phone" class="input" maxlength="40" value="{{ old('phone', $business->getAttribute('phone')) }}">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.email') }}</label>
                    <input name="email" type="email" class="input" maxlength="160" value="{{ old('email', $business->getAttribute('email')) }}">
                </div>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.website') }}</label>
                    <input name="website" class="input" maxlength="200" value="{{ old('website', $business->getAttribute('website')) }}">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.booking_url') }}</label>
                    <input name="booking_url" class="input" maxlength="200" value="{{ old('booking_url', $business->getAttribute('booking_url')) }}">
                </div>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.address') }}</label>
                    <input name="address_line" class="input" maxlength="160" value="{{ old('address_line', $business->getAttribute('address_line')) }}">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.postcode_label') }}</label>
                    <input name="postcode" class="input" maxlength="12" value="{{ old('postcode', $business->getAttribute('postcode')) }}">
                </div>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.social_url') }}</label>
                    <input name="social_url" class="input" maxlength="200" value="{{ old('social_url', $business->getAttribute('social_url')) }}">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.licence_number') }}</label>
                    <input name="licence_number" class="input" maxlength="80" value="{{ old('licence_number', $business->getAttribute('licence_number')) }}">
                </div>
            </div>
            <div class="field">
                <label class="field__label">{{ __('directory::messages.description') }}</label>
                <textarea name="description" class="textarea" rows="5" maxlength="{{ $paid ? 3000 : 300 }}">{{ old('description', $business->getAttribute('description')) }}</textarea>
                @unless($paid)<p class="field__hint">{{ __('directory::messages.description_free_hint') }}</p>@endunless
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.opening_hours') }}</label>
                    <textarea name="opening_hours" class="textarea" rows="5" maxlength="600" placeholder="Mon-Fri 8:30-18:30&#10;Sat 9:00-13:00&#10;Sun closed">{{ old('opening_hours', $business->getAttribute('opening_hours')) }}</textarea>
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.services') }}</label>
                    <textarea name="services_text" class="textarea" rows="5" maxlength="1500">{{ old('services_text', implode("\n", $business->serviceList())) }}</textarea>
                    <p class="field__hint">{{ __('directory::messages.services_hint') }}</p>
                </div>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.price_from') }}</label>
                    <input name="price_from" class="input" maxlength="60" value="{{ old('price_from', $business->getAttribute('price_from')) }}">
                    <p class="field__hint">{{ __('directory::messages.price_from_hint') }}</p>
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.accreditation') }}</label>
                    <select name="accreditation" class="select">
                        <option value="">{{ __('directory::messages.accreditation_none') }}</option>
                        @foreach(\Modules\Directory\Models\Business::ACCREDITATIONS as $key => $label)
                            <option value="{{ $key }}" @selected(old('accreditation', $business->getAttribute('accreditation')) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="field__hint">{{ __('directory::messages.accreditation_hint') }}</p>
                </div>
            </div>
            <div class="row row--wrap">
                <label class="row"><input type="checkbox" name="emergency_care" value="1" @checked($business->getAttribute('emergency_care'))> {{ __('directory::messages.emergency_care') }}</label>
                <label class="row"><input type="checkbox" name="home_visits" value="1" @checked($business->getAttribute('home_visits'))> {{ __('directory::messages.home_visits') }}</label>
                <label class="row"><input type="checkbox" name="cat_only" value="1" @checked($business->getAttribute('cat_only'))> {{ __('directory::messages.cat_only') }}</label>
            </div>
            <div class="field">
                <label class="field__label">{{ __('directory::messages.new_client_offer') }}</label>
                <input name="new_client_offer" class="input" maxlength="160" value="{{ old('new_client_offer', $business->getAttribute('new_client_offer')) }}" @disabled(! $paid)>
                <p class="field__hint">{{ __('directory::messages.new_client_offer_hint') }}</p>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.logo') }}</label>
                    @if($logo = $business->logoUrl())<img src="{{ $logo }}" alt="" class="business-logo">@endif
                    <input type="file" name="logo" accept="image/*" class="input">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.photos') }}</label>
                    <input type="file" name="photos[]" accept="image/*" multiple class="input">
                    <p class="field__hint">{{ __('directory::messages.photos_hint', ['count' => $business->photoLimit()]) }}</p>
                </div>
            </div>
            @if($business->photoUrls() !== [])
                <div class="business-photos">
                    @foreach((array) $business->getAttribute('photos') as $index => $path)
                        <label class="business-photos__item">
                            <img src="{{ asset('storage/'.$path) }}" alt="">
                            <span class="row"><input type="checkbox" name="remove_photos[]" value="{{ $path }}"> {{ __('directory::messages.remove_photo') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
            @if($errors->any())<div class="notice notice--caution"><p>{{ $errors->first() }}</p></div>@endif
            <div class="row row--wrap">
                <button type="submit" class="button button--primary">{{ __('directory::messages.save') }}</button>
                @if($business->getAttribute('is_published'))
                    <a href="{{ route('directory.show', [$business->getAttribute('type'), $business->getAttribute('slug')]) }}" class="button button--ghost">{{ __('directory::messages.view_page') }}</a>
                @endif
            </div>
        </form>
    </section>
@empty
    <x-ui.empty-state icon="building" :title="__('directory::messages.no_business')" :text="__('directory::messages.add_business_lead')">
        <a href="{{ route('directory.create') }}" class="button button--secondary">{{ __('directory::messages.add_business') }}</a>
    </x-ui.empty-state>
@endforelse

<section class="card">
    <div class="card__body stack stack--tight">
        <p class="title-card">{{ __('directory::messages.upgrade') }}</p>
        <p class="text-muted">{{ __('directory::messages.upgrade_lead') }}</p>
        <a href="{{ route('billing.membership.index') }}" class="button button--secondary">{{ __('billing::messages.membership') }}</a>
    </div>
</section>
@endsection
