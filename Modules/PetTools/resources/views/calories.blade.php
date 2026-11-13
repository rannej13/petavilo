@extends('site::layouts.app')

@section('title', 'Cat and dog calorie calculator: how much should I feed?')
@section('description', 'Work out daily calories and grams of food for your cat or dog using the vets\' RER formula, plus a 14-day plan for switching food.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Calorie calculator</p>
            <h1 class="title-page">How much should I feed?</h1>
            <p class="text-lead">Uses the same starting formula vets use. Weigh your pet and check the calories on the food pack for the most accurate answer.</p>
        </header>

        <form method="GET" class="card"><div class="card__body stack">
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label" for="species">Pet</label>
                    <select id="species" name="species" class="select" onchange="this.form.submit()">
                        <option value="cat" @selected($species === 'cat')>Cat</option>
                        <option value="dog" @selected($species === 'dog')>Dog</option>
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="weight">Current weight (kg)</label>
                    <input id="weight" name="weight" type="number" step="0.1" min="0.2" max="100" class="input" required value="{{ $weight }}">
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="stage">Which fits best?</label>
                <select id="stage" name="stage" class="select">
                    @foreach($factors[$species] as $key => $option)
                        <option value="{{ $key }}" @selected($stage === $key)>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label" for="kcal">New food: calories per 100 g</label>
                    <input id="kcal" name="kcal" type="number" step="1" min="20" max="700" class="input" value="{{ $density }}" placeholder="On the pack, e.g. 85">
                    <p class="field__hint">Wet food is often 70 to 110. Dry is often 330 to 420.</p>
                </div>
                <div class="field">
                    <label class="field__label" for="old_kcal">Current food: calories per 100 g (optional)</label>
                    <input id="old_kcal" name="old_kcal" type="number" step="1" min="20" max="700" class="input" value="{{ $oldDensity }}">
                </div>
            </div>
            @if($species === 'cat')
                <div class="field__row field__row--two">
                    <div class="field">
                        <label class="field__label" for="rejected">Texture your cat refuses (optional)</label>
                        <select id="rejected" name="rejected" class="select">
                            <option value="">None</option>
                            @foreach($textures as $key => $label)<option value="{{ $key }}" @selected($rejected === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="field__label" for="protein">Favourite flavour (optional)</label>
                        <input id="protein" name="protein" class="input" maxlength="30" placeholder="e.g. chicken, tuna" value="{{ $protein }}">
                    </div>
                </div>
            @endif
            <button type="submit" class="button button--primary">Calculate</button>
        </div></form>

        @if($result)
            <section class="stack" id="result">
                <div class="grid grid--three">
                    <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Per day</p><p class="title-page">{{ number_format($result['kcal']) }} kcal</p></div></div>
                    @if($result['grams'])
                        <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Of this food</p><p class="title-page">{{ number_format($result['grams']) }} g</p></div></div>
                    @endif
                    <div class="card"><div class="card__body stack stack--tight"><p class="text-eyebrow">Resting energy</p><p class="title-page">{{ number_format($result['rer']) }} kcal</p><p class="text-meta">70 × weight<sup>0.75</sup> × {{ $result['factor'] }}</p></div></div>
                </div>
                <p class="text-muted">Split it into 2 to 4 meals and include treats in the total. Weigh your pet every 2 to 4 weeks and adjust by 10% if they gain or lose. For weight loss, use your pet's target weight and ask your vet nurse for a weigh-in plan.</p>

                @if($result['schedule'] !== [])
                    <div class="card"><div class="card__body stack stack--tight">
                        <h2 class="card__title">14-day switch-over plan (per day)</h2>
                        <ul class="welfare-list">
                            @foreach($result['schedule'] as $row)
                                <li class="welfare-list__item"><span>{{ $row['label'] }}</span><strong>{{ $row['old_grams'] }} g old + {{ $row['new_grams'] }} g new</strong></li>
                            @endforeach
                        </ul>
                        <p class="text-meta">Go slower if you see loose stools. Never let a cat go without eating for more than a day; if they refuse the new food, go back a step.</p>
                        <button type="button" class="button button--ghost button--small" onclick="window.print()">Print this plan</button>
                    </div></div>
                @endif
            </section>
        @endif

        @if($matches !== [])
            <section class="card"><div class="card__body stack stack--tight">
                <h2 class="card__title">Textures to try instead</h2>
                <p class="text-muted">Cats that refuse {{ strtolower($textures[$rejected]) }} often accept these. Buy a few small packs first.</p>
                <div class="row row--wrap">
                    @foreach($matches as $match)
                        <a class="button button--secondary button--small" href="{{ $match['url'] }}" rel="nofollow sponsored noopener" target="_blank">{{ $match['label'] }}{{ $protein !== '' ? ' · '.$protein : '' }}</a>
                    @endforeach
                </div>
                @if($affiliate)<p class="text-meta">Affiliate links. We may earn a small commission at no cost to you.</p>@endif
            </div></section>
        @endif

        <p class="text-meta">A starting point only. Needs vary by up to 20% between pets. Pets with kidney disease, diabetes or other conditions need a diet plan from their vet.</p>
    </div>
</div>
@endsection
