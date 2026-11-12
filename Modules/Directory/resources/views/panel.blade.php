@extends('panel::layouts.panel', ['panelSection' => 'business'])

@section('title', __('directory::messages.my_business'))

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">{{ __('directory::messages.my_business') }}</h1>
    </div>
    <a href="{{ route('directory.create') }}" class="button button--ghost">{{ __('directory::messages.add_business') }}</a>
</header>

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
        <form method="POST" action="{{ route('directory.panel.update', $business) }}" class="card__body stack">
            @csrf
            @method('PUT')
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.phone') }}</label>
                    <input name="phone" class="input" maxlength="40" value="{{ $business->getAttribute('phone') }}">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.email') }}</label>
                    <input name="email" type="email" class="input" maxlength="160" value="{{ $business->getAttribute('email') }}">
                </div>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.website') }}</label>
                    <input name="website" class="input" maxlength="200" value="{{ $business->getAttribute('website') }}">
                </div>
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.address') }}</label>
                    <input name="address_line" class="input" maxlength="160" value="{{ $business->getAttribute('address_line') }}">
                </div>
            </div>
            <div class="field">
                <label class="field__label">{{ __('directory::messages.licence_number') }}</label>
                <input name="licence_number" class="input" maxlength="80" value="{{ $business->getAttribute('licence_number') }}">
            </div>
            <div class="field">
                <label class="field__label">{{ __('directory::messages.description') }}</label>
                <textarea name="description" class="textarea" rows="5" maxlength="{{ $paid ? 3000 : 300 }}">{{ $business->getAttribute('description') }}</textarea>
                @unless($paid)<p class="field__hint">{{ __('directory::messages.description_free_hint') }}</p>@endunless
            </div>
            @if($paid)
                <div class="field">
                    <label class="field__label">{{ __('directory::messages.opening_hours') }}</label>
                    <textarea name="opening_hours" class="textarea" rows="4" maxlength="600">{{ $business->getAttribute('opening_hours') }}</textarea>
                </div>
            @endif
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
