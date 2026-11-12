@extends('site::layouts.app')

@section('title', __('directory::messages.claim_title').': '.$business->getAttribute('name'))

@section('content')
<div class="shell shell--form page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow">{{ __('directory::messages.claim_title') }}</p>
            <h1 class="title-page">{{ $business->getAttribute('name') }}</h1>
            <p class="text-meta">{{ collect([$business->getAttribute('address_line'), $business->getAttribute('town'), $business->getAttribute('postcode')])->filter()->implode(', ') }}</p>
        </header>

        @if($business->isClaimed())
            <div class="notice"><p>{{ __('directory::messages.already_claimed') }}</p></div>
        @else
            @include('directory::partials.claim-benefits')

            <section class="card">
                <div class="card__body stack">
                    <p>{{ __('directory::messages.claim_token_lead') }}</p>
                    @if($guest)
                        <div class="row row--wrap">
                            <a href="{{ route('register') }}" class="button button--primary">{{ __('directory::messages.claim_register') }}</a>
                            <a href="{{ route('login') }}" class="button button--ghost">{{ __('directory::messages.claim_login') }}</a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('directory.claim.token.store', $token) }}">
                            @csrf
                            <button type="submit" class="button button--primary">{{ __('directory::messages.claim_confirm') }}</button>
                        </form>
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
