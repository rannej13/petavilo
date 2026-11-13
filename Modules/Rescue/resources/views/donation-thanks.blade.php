@extends('site::layouts.app')

@section('title', 'Thank you for your donation')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @if($donation->getAttribute('status') === 'paid')
            <header class="stack stack--tight">
                <p class="text-eyebrow">Thank you</p>
                <h1 class="title-page">You donated {{ $donation->totalLabel() }} to {{ $donation->organisation?->name }}</h1>
                <p class="text-lead">{{ $donation->amountLabel() }} goes in full to {{ $donation->organisation?->name }}.</p>
            </header>
            <ul class="welfare-list">
                <li class="welfare-list__item"><span>Reference</span><strong>D{{ str_pad((string) $donation->getKey(), 6, '0', STR_PAD_LEFT) }}</strong></li>
                <li class="welfare-list__item"><span>Donation to the rescue</span><strong>{{ $donation->amountLabel() }}</strong></li>
                @if((float) $donation->getAttribute('fee_cover') > 0)
                    <li class="welfare-list__item"><span>Card fee you covered</span><strong>{{ $donation->feeCoverLabel() }}</strong></li>
                @endif
                @if((float) $donation->getAttribute('tip') > 0)
                    <li class="welfare-list__item"><span>Your tip to {{ config('catmarket.site_name') }}</span><strong>{{ $donation->tipLabel() }}</strong></li>
                @endif
                <li class="welfare-list__item"><span>Total paid</span><strong>{{ $donation->totalLabel() }}</strong></li>
                <li class="welfare-list__item"><span>Paid to</span><strong>{{ $donation->organisation?->name }}@if($donation->organisation?->charity_number) · {{ $donation->organisation?->country === 'US' ? 'EIN' : 'Charity' }} {{ $donation->organisation?->charity_number }}@endif</strong></li>
                <li class="welfare-list__item"><span>Date</span><strong>{{ $donation->getAttribute('paid_at')?->isoFormat('D MMMM YYYY') }}</strong></li>
            </ul>
            <p class="text-muted">Stripe emails a card receipt. Keep this page as a record of what went to the rescue.</p>
        @else
            <header class="stack stack--tight">
                <h1 class="title-page">We are checking your payment</h1>
                <p class="text-lead">This can take a minute. Refresh this page shortly. If you were not charged, no donation was taken.</p>
            </header>
        @endif
        @if($donation->organisation)
            <a href="{{ route('rescue.show', $donation->organisation->slug) }}" class="button button--secondary">Back to {{ $donation->organisation->name }}</a>
        @endif
    </div>
</div>
@endsection
