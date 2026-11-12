@extends('site::layouts.app')

@php
    $isPaid = $business->isPaid();
    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => $business->getAttribute('type') === 'vets' ? 'VeterinaryCare' : 'LocalBusiness',
        'name' => (string) $business->getAttribute('name'),
        'telephone' => $business->getAttribute('phone'),
        'url' => $business->websiteUrl(),
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $business->getAttribute('address_line'),
            'addressLocality' => $business->getAttribute('town'),
            'postalCode' => $business->getAttribute('postcode'),
            'addressCountry' => (string) $business->getAttribute('country'),
        ]),
    ]);
@endphp

@section('title', $business->getAttribute('name').($business->getAttribute('town') ? ', '.$business->getAttribute('town') : ''))
@section('description', \Illuminate\Support\Str::limit((string) ($business->getAttribute('description') ?: $typeName.' in '.$business->getAttribute('town')), 155))

@section('content')
<div class="shell shell--narrow page">
    <article class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('directory.index') }}">{{ __('directory::messages.directory') }}</a>
            <span class="breadcrumb__separator">/</span>
            <a href="{{ route('directory.type', $business->getAttribute('type')) }}">{{ $typeName }}</a>
        </nav>

        <header class="stack stack--tight">
            <h1 class="title-page">{{ $business->getAttribute('name') }}</h1>
            <p class="text-meta">
                {{ \Modules\Directory\Models\Business::typeName((string) $business->getAttribute('type'), true) }}
                @if($business->getAttribute('town')) · {{ $business->getAttribute('town') }}@endif
                @if($business->getAttribute('is_verified')) · <span class="badge badge--positive">{{ __('directory::messages.verified') }}</span>@endif
            </p>
        </header>

        <section class="card">
            <div class="card__body stack">
                @if(filled($business->getAttribute('description')))
                    <div class="prose">{!! nl2br(e($business->getAttribute('description'))) !!}</div>
                @endif
                <ul class="welfare-list">
                    @if(filled($business->getAttribute('phone')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.phone') }}</span><a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $business->getAttribute('phone')) }}">{{ $business->getAttribute('phone') }}</a></li>
                    @endif
                    @if($business->websiteUrl())
                        <li class="welfare-list__item"><span>{{ __('directory::messages.website') }}</span><a class="text-link" href="{{ $business->websiteUrl() }}" rel="noopener {{ $isPaid ? '' : 'nofollow' }}" target="_blank">{{ parse_url($business->websiteUrl(), PHP_URL_HOST) }}</a></li>
                    @endif
                    @if(filled($business->getAttribute('email')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.email') }}</span><a class="text-link" href="mailto:{{ $business->getAttribute('email') }}">{{ $business->getAttribute('email') }}</a></li>
                    @endif
                    @if(filled($business->getAttribute('address_line')) || filled($business->getAttribute('postcode')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.address') }}</span><span>{{ collect([$business->getAttribute('address_line'), $business->getAttribute('town'), $business->getAttribute('postcode')])->filter()->implode(', ') }}</span></li>
                    @endif
                    @if(filled($business->getAttribute('licence_number')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.licence_number') }}</span><span>{{ $business->getAttribute('licence_number') }}</span></li>
                    @endif
                    @if($isPaid && filled($business->getAttribute('opening_hours')))
                        <li class="welfare-list__item"><span>{{ __('directory::messages.opening_hours') }}</span><span>{!! nl2br(e($business->getAttribute('opening_hours'))) !!}</span></li>
                    @endif
                </ul>
            </div>
        </section>

        @unless($isPaid)
            <x-ads::slot name="directory"/>
        @endunless

        @unless($business->isClaimed())
            <section class="card">
                <div class="card__head"><h2 class="card__title">{{ __('directory::messages.claim_title') }}</h2></div>
                <div class="card__body stack">
                    <p class="text-muted">{{ __('directory::messages.claim_lead') }}</p>
                    @auth
                        @if($hasPendingClaim)
                            <p class="badge badge--caution">{{ __('directory::messages.claim_pending') }}</p>
                        @else
                            <form method="POST" action="{{ route('directory.claim', [$business->getAttribute('type'), $business->getAttribute('slug')]) }}" class="stack">
                                @csrf
                                <div class="field__row field__row--two">
                                    <div class="field">
                                        <label class="field__label" for="claim-role">{{ __('directory::messages.role') }}</label>
                                        <input id="claim-role" name="role_at_business" class="input" required maxlength="80" placeholder="Owner, practice manager...">
                                    </div>
                                    <div class="field">
                                        <label class="field__label" for="claim-phone">{{ __('directory::messages.phone') }}</label>
                                        <input id="claim-phone" name="phone" class="input" required maxlength="40" type="tel">
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="field__label" for="claim-message">{{ __('directory::messages.message') }}</label>
                                    <textarea id="claim-message" name="message" class="textarea" rows="3" maxlength="1000"></textarea>
                                </div>
                                <button class="button button--secondary" type="submit">{{ __('directory::messages.claim_submit') }}</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="button button--secondary">{{ __('directory::messages.claim_submit') }}</a>
                    @endauth
                </div>
            </section>
        @endunless
    </article>
</div>
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection
