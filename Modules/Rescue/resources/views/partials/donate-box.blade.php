@php $currencySymbol = $organisation->currency() === 'USD' ? '$' : '£'; @endphp
<section class="card" id="donate"><div class="card__body stack stack--tight">
    <h2 class="card__title">Support {{ $organisation->getAttribute('name') }}</h2>
    <p class="text-muted">100% of your donation goes to {{ $organisation->getAttribute('name') }}. Stripe passes it to the rescue's own account automatically. {{ config('catmarket.site_name') }} is paid only by the optional tip below.</p>
    <form method="POST" action="{{ route('rescue.donate', $organisation->getAttribute('slug')) }}" class="stack stack--tight">
        @csrf
        {{ \App\Support\SpamGuard::fields() }}
        <div class="row row--wrap" role="radiogroup" aria-label="Amount">
            @foreach((array) config('rescue.donation_presets', [5, 10, 25, 50]) as $preset)
                <label class="pill"><input type="radio" name="amount" value="{{ $preset }}" @checked((int) old('amount', 10) === $preset)> {{ $currencySymbol }}{{ $preset }}</label>
            @endforeach
        </div>
        <div class="field">
            <label class="field__label" for="donate-other">Or another amount ({{ $currencySymbol }})</label>
            <input id="donate-other" type="number" min="{{ config('rescue.donation_min', 2) }}" max="{{ config('rescue.donation_max', 5000) }}" step="1" class="input" inputmode="numeric" oninput="if(this.value){this.form.querySelectorAll('input[name=amount][type=radio]').forEach(function(r){r.checked=false});this.name='amount'}else{this.name=''}">
            @error('amount')<p class="field__error">{{ $message }}</p>@enderror
        </div>
        <label class="row"><input type="checkbox" name="cover_fees" value="1" checked> <span>Cover the card processing fee (about {{ $organisation->currency() === 'USD' ? '2.9% + 30¢' : '1.5% + 20p' }})</span></label>
        <div class="field">
            <label class="field__label" for="donate-tip">Optional tip to {{ config('catmarket.site_name') }}</label>
            <select id="donate-tip" name="tip_percent" class="select">
                @foreach([10 => '10%', 5 => '5%', 15 => '15%', 0 => 'No tip'] as $percent => $label)
                    <option value="{{ $percent }}" @selected((int) old('tip_percent', 10) === $percent)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="field__hint">Tips keep {{ config('catmarket.site_name') }} free for rescues. Choose No tip and the rescue still gets every penny of your donation.</p>
        </div>
        <div class="field">
            <label class="field__label" for="donate-name">Your name (optional)</label>
            <input id="donate-name" name="donor_name" class="input" maxlength="80" value="{{ old('donor_name', auth()->user()?->name) }}">
        </div>
        <div class="field">
            <label class="field__label" for="donate-message">Message to the rescue (optional)</label>
            <input id="donate-message" name="message" class="input" maxlength="300" value="{{ old('message') }}">
        </div>
        <label class="row"><input type="checkbox" name="is_anonymous" value="1"> <span>Keep my name private from the rescue</span></label>
        <button type="submit" class="button button--primary">Donate by card</button>
        <p class="text-meta">Secure payment by Stripe. You will see the donation, card fee and tip as separate lines before you pay. {{ config('catmarket.site_name') }} receives no part of the donation itself, only any tip you add. Donating never affects which animals are shown or in what order. UK taxpayers: ask the rescue about Gift Aid.</p>
    </form>
</div></section>
