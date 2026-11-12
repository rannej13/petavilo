@extends('panel::layouts.panel', ['panelSection' => 'membership'])

@section('title', __('billing::messages.membership'))

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">{{ __('billing::messages.membership') }}</h1>
        <p class="text-muted">{{ __('billing::messages.membership_lead') }}</p>
    </div>
    @if($hasStripeCustomer)
        <a href="{{ route('billing.membership.portal') }}" class="button button--ghost">{{ __('billing::messages.manage_billing') }}</a>
    @endif
</header>

@if(request()->boolean('welcome'))
    <div class="card"><div class="card__body"><p>{{ __('billing::messages.welcome') }}</p></div></div>
@endif

@unless($paymentsReady)
    <div class="card"><div class="card__body"><p class="text-muted">{{ __('billing::messages.payments_unavailable') }}</p></div></div>
@endunless

<div class="chip-nav">
    <a href="{{ route('billing.membership.index', ['currency' => 'GBP']) }}" class="pill {{ $currency === 'GBP' ? 'is-active' : '' }}">£ UK</a>
    <a href="{{ route('billing.membership.index', ['currency' => 'USD']) }}" class="pill {{ $currency === 'USD' ? 'is-active' : '' }}">$ US</a>
</div>

<div class="grid grid--three">
    @foreach($plans as $plan)
        @php $isActive = in_array($plan['key'], $activeKeys, true); @endphp
        <section class="card">
            <div class="card__head">
                <h2 class="card__title">{{ __('billing::messages.plan_'.$plan['key']) }}</h2>
                @if($isActive)<span class="badge badge--positive">{{ __('billing::messages.current_plan') }}</span>@endif
            </div>
            <div class="card__body stack">
                <p class="text-muted">{{ __('billing::messages.plan_'.$plan['key'].'_text') }}</p>
                <p class="title-section">{{ \Modules\Billing\Support\MembershipCatalog::amountFor($plan, $currency) }} <span class="text-meta">{{ __('billing::messages.per_month') }}</span></p>
                <ul class="stack stack--tight">
                    @foreach(explode('|', __('billing::messages.plan_'.$plan['key'].'_benefits')) as $benefit)
                        <li>{{ $benefit }}</li>
                    @endforeach
                </ul>
                @unless($isActive)
                    <form method="POST" action="{{ route('billing.membership.checkout', $plan['key']) }}">
                        @csrf
                        <input type="hidden" name="currency" value="{{ $currency }}">
                        <button type="submit" class="button button--primary button--block" @disabled(! $paymentsReady || \Modules\Billing\Support\MembershipCatalog::priceIdFor($plan, $currency) === null)>
                            {{ __('billing::messages.subscribe') }}
                        </button>
                    </form>
                @endunless
            </div>
        </section>
    @endforeach
</div>
@endsection
