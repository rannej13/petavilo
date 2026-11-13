@extends('site::layouts.app')

@section('title', 'Pet insurance or a savings pot? 5-year vet bill simulator')
@section('description', 'Compare paying for pet insurance with saving monthly, against a big vet bill, over five years.')

@php $money = static fn (float $value): string => ($value < 0 ? '-' : '').$symbol.number_format(abs($value), 0); @endphp

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Vet bill simulator</p>
            <h1 class="title-page">Pet insurance or a savings pot?</h1>
            <p class="text-lead">See what five years looks like both ways if your pet needs one big operation.</p>
        </header>

        <form method="GET" class="card"><div class="card__body stack">
            <div class="field__row field__row--two">
                <div class="field"><label class="field__label" for="saving">I could save each month ({{ $symbol }})</label><input id="saving" name="saving" type="number" min="0" step="1" class="input" value="{{ $input['saving'] }}"></div>
                <div class="field"><label class="field__label" for="premium">Insurance quote per month ({{ $symbol }})</label><input id="premium" name="premium" type="number" min="0" step="1" class="input" value="{{ $input['premium'] }}"></div>
            </div>
            <div class="field__row field__row--three">
                <div class="field"><label class="field__label" for="increase">Yearly price rise (%)</label><input id="increase" name="increase" type="number" min="0" max="50" step="1" class="input" value="{{ $input['increase'] }}"></div>
                <div class="field"><label class="field__label" for="excess">Excess ({{ $symbol }})</label><input id="excess" name="excess" type="number" min="0" step="1" class="input" value="{{ $input['excess'] }}"></div>
                <div class="field"><label class="field__label" for="limit">Vet fee limit ({{ $symbol }})</label><input id="limit" name="limit" type="number" min="0" step="100" class="input" value="{{ $input['limit'] }}"></div>
            </div>
            <div class="field__row field__row--two">
                <div class="field"><label class="field__label" for="bill">Big vet bill ({{ $symbol }})</label><input id="bill" name="bill" type="number" min="0" step="50" class="input" value="{{ $input['bill'] }}"><p class="field__hint">Example only. Surgery for a blocked bladder or a swallowed object often costs thousands. Ask your vet for local prices.</p></div>
                <div class="field"><label class="field__label" for="year">Happens in year</label><select id="year" name="year" class="select">@foreach([0 => 'Never', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'] as $value => $label)<option value="{{ $value }}" @selected((int) $input['year'] === $value)>{{ $label }}</option>@endforeach</select></div>
            </div>
            <div class="field__row field__row--two">
                <div class="field"><label class="field__label" for="age">Pet's age</label><select id="age" name="age" class="select"><option value="">Choose</option>@foreach(['young' => 'Under 4', 'middle' => '4 to 8', 'older' => '9 or older'] as $value => $label)<option value="{{ $value }}" @selected($age === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label class="field__label" for="conditions">Any past illness or injury?</label><select id="conditions" name="conditions" class="select"><option value="">Choose</option><option value="no" @selected($conditions === 'no')>No</option><option value="yes" @selected($conditions === 'yes')>Yes</option></select></div>
            </div>
            <button type="submit" class="button button--primary">Compare</button>
        </div></form>

        <section class="stack">
            <h2 class="title-section">After 5 years</h2>
            <div class="grid grid--three">
                <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Savings pot left</p><p class="title-page">{{ $money($result['savings_left']) }}</p></div></div>
                <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Total paid with insurance</p><p class="title-page">{{ $money($result['insured_total']) }}</p><p class="text-meta">Premiums, excess and anything over the limit</p></div></div>
                @if($result['shortfall'] > 0)
                    <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Savings shortfall when the bill comes</p><p class="title-page">{{ $money($result['shortfall']) }}</p><p class="text-meta">What you'd need to find on the day</p></div></div>
                @endif
            </div>

            <div class="card"><div class="card__body stack stack--tight">
                <h3 class="title-card">Year by year</h3>
                <svg viewBox="0 0 500 220" role="img" aria-label="Savings balance and insurance cost by year" style="width:100%;height:auto">
                    @php $mid = 110; $scale = 90 / $result['max']; @endphp
                    <line x1="30" y1="{{ $mid }}" x2="490" y2="{{ $mid }}" stroke="currentColor" stroke-opacity="0.25"/>
                    @foreach($result['rows'] as $i => $row)
                        @php $x = 50 + $i * 90; $s = $row['savings_balance'] * $scale; $c = $row['insured_cost'] * $scale; @endphp
                        <rect x="{{ $x }}" y="{{ $s >= 0 ? $mid - $s : $mid }}" width="28" height="{{ max(1, abs($s)) }}" fill="#2e7d5b" rx="3"><title>Savings: {{ $money($row['savings_balance']) }}</title></rect>
                        <rect x="{{ $x + 32 }}" y="{{ $mid - $c }}" width="28" height="{{ max(1, $c) }}" fill="#7c3aed" rx="3"><title>Insurance paid: {{ $money($row['insured_cost']) }}</title></rect>
                        <text x="{{ $x + 30 }}" y="215" text-anchor="middle" font-size="12" fill="currentColor">Year {{ $row['year'] }}</text>
                    @endforeach
                </svg>
                <p class="text-meta"><span style="color:#2e7d5b">■</span> Savings pot balance (below the line means you are short) · <span style="color:#7c3aed">■</span> Total paid with insurance</p>
                <ul class="welfare-list">
                    @foreach($result['rows'] as $row)
                        <li class="welfare-list__item"><span>Year {{ $row['year'] }}</span><strong>Savings {{ $money($row['savings_balance']) }} · Insurance {{ $money($row['insured_cost']) }}</strong></li>
                    @endforeach
                </ul>
            </div></div>
        </section>

        <section class="stack stack--tight">
            <h2 class="title-section">Things to know</h2>
            @if($conditions === 'yes')
                <div class="notice notice--caution"><p>Most policies will not cover conditions your pet had before you took the policy out. Read the exclusions carefully, or keep saving for those.</p></div>
            @elseif($age === 'young' && $conditions === 'no')
                <div class="notice notice--accent"><p>A young pet with no past problems usually gets the widest cover. Lifetime policies started now can keep covering long-term conditions in later years.</p></div>
            @endif
            <ul class="stack stack--tight">
                <li><strong>Lifetime</strong> policies renew cover for ongoing conditions each year. <strong>Time-limited</strong> and <strong>maximum benefit</strong> policies stop paying for a condition after a set time or amount.</li>
                <li>Check for waiting periods (often 14 days for illness), dental cover, and whether the excess is per condition or per year.</li>
                <li>Prices usually go up as your pet gets older.</li>
            </ul>
        </section>

        @if($insurers !== [])
            <section class="card"><div class="card__body stack stack--tight">
                <h2 class="card__title">Get a quote</h2>
                <div class="row row--wrap">
                    @foreach($insurers as $insurer)
                        <a class="button button--secondary" href="{{ $insurer['url'] }}" rel="sponsored noopener" target="_blank">{{ $insurer['name'] }}</a>
                    @endforeach
                </div>
                <p class="text-meta">Partner links. We may be paid if you buy a policy. This list is not a recommendation and does not cover the whole market.</p>
            </div></section>
        @endif

        <p class="text-meta">A rough guide for planning only, not financial or insurance advice. {{ config('catmarket.site_name') }} is not authorised by the Financial Conduct Authority and is not a licensed insurance producer. Always read the policy documents before you buy.</p>
    </div>
</div>
@endsection
