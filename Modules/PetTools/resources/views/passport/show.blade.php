@extends('panel::layouts.panel', ['panelSection' => 'pets'])

@section('title', $pet->getAttribute('name'))

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">{{ $pet->getAttribute('name') }}</h1>
        <p class="text-muted">{{ $pet->speciesLabel() }}@if($pet->getAttribute('breed')) · {{ $pet->getAttribute('breed') }}@endif @if($age = $pet->ageLabel()) · {{ $age }}@endif</p>
    </div>
    <div class="row">
        <a href="{{ route('pettools.passport.edit', $pet->getKey()) }}" class="button button--secondary"><x-ui.icon name="edit"/><span>Edit</span></a>
    </div>
</header>

@if($pet->getAttribute('is_missing'))
    <div class="notice notice--caution"><p class="notice__title">{{ $pet->getAttribute('name') }} is marked as missing</p>
        <p>Your emergency pass asks finders to call you. <a class="text-link" href="{{ route('panel.listings.create') }}">Post a free Lost advert</a> and <a class="text-link" href="{{ route('rescue.lost-found') }}">check found pets</a>.</p></div>
@endif

<div class="grid grid--split">
    <div class="stack">
        @if($photo = $pet->photoUrl())
            <img src="{{ $photo }}" alt="{{ $pet->getAttribute('name') }}" class="card__image" style="border-radius:12px;max-height:320px;object-fit:cover">
        @endif

        <section class="card"><div class="card__body stack stack--tight">
            <h2 class="card__title">Health</h2>
            <ul class="welfare-list">
                <li class="welfare-list__item"><span>Microchip</span><strong>{{ $pet->maskedMicrochip() ?? 'Not added' }}@if($pet->getAttribute('microchip_database')) · {{ $pet->getAttribute('microchip_database') }}@endif</strong></li>
                <li class="welfare-list__item"><span>Neutered</span><strong>{{ $pet->getAttribute('neutered') ?: 'Not set' }}</strong></li>
                @if($weight = $pet->latestWeight())
                    <li class="welfare-list__item"><span>Weight</span><strong>{{ rtrim(rtrim((string) $weight->getAttribute('value'), '0'), '.') }} kg · {{ $weight->getAttribute('happened_on')?->isoFormat('D MMM YYYY') }}</strong></li>
                @endif
                @foreach(['allergies' => 'Allergies', 'medications' => 'Medicines', 'medical_notes' => 'Notes', 'diet' => 'Diet'] as $field => $label)
                    @if(filled($pet->getAttribute($field)))<li class="welfare-list__item"><span>{{ $label }}</span><strong>{{ $pet->getAttribute($field) }}</strong></li>@endif
                @endforeach
                @if($pet->getAttribute('vet_name'))
                    <li class="welfare-list__item"><span>Vet</span><strong>{{ $pet->getAttribute('vet_name') }}@if($pet->getAttribute('vet_phone')) · <a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $pet->getAttribute('vet_phone')) }}">{{ $pet->getAttribute('vet_phone') }}</a>@endif</strong></li>
                @endif
            </ul>
        </div></section>

        <section class="card"><div class="card__body stack stack--tight">
            <h2 class="card__title">Insurance</h2>
            @if($pet->getAttribute('insurer'))
                <ul class="welfare-list">
                    <li class="welfare-list__item"><span>Insurer</span><strong>{{ $pet->getAttribute('insurer') }}@if($pet->getAttribute('policy_type')) · {{ $pet->getAttribute('policy_type') }}@endif</strong></li>
                    @if($pet->getAttribute('policy_number'))<li class="welfare-list__item"><span>Policy number</span><strong>{{ $pet->getAttribute('policy_number') }}</strong></li>@endif
                    @if($pet->getAttribute('policy_renewal'))<li class="welfare-list__item"><span>Renews</span><strong>{{ $pet->getAttribute('policy_renewal')->isoFormat('D MMM YYYY') }}</strong></li>@endif
                    @if($pet->getAttribute('policy_excess') !== null)<li class="welfare-list__item"><span>Excess</span><strong>{{ number_format((float) $pet->getAttribute('policy_excess'), 2) }}</strong></li>@endif
                    @if($pet->getAttribute('policy_limit') !== null)<li class="welfare-list__item"><span>Vet fee limit</span><strong>{{ number_format((float) $pet->getAttribute('policy_limit'), 2) }}</strong></li>@endif
                    @if($pet->getAttribute('insurer_claims_phone'))<li class="welfare-list__item"><span>Claims</span><strong><a class="text-link" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $pet->getAttribute('insurer_claims_phone')) }}">{{ $pet->getAttribute('insurer_claims_phone') }}</a></strong></li>@endif
                </ul>
                <p class="text-meta">Log claims below as "Insurance claim" records to keep track of what you have claimed this year.</p>
            @else
                <p class="text-muted">No insurance details yet. <a class="text-link" href="{{ route('pettools.passport.edit', $pet->getKey()) }}">Add them</a> to keep your renewal date and claims number handy.</p>
            @endif
        </div></section>

        <section class="card">
            <div class="card__head"><h2 class="card__title">Records</h2></div>
            <form method="POST" action="{{ route('pettools.passport.records.store', $pet->getKey()) }}" class="card__body stack stack--tight">
                @csrf
                <div class="field__row field__row--two">
                    <div class="field"><label class="field__label" for="record-type">Type</label><select id="record-type" name="type" class="select">@foreach($types as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="field"><label class="field__label" for="record-title">What</label><input id="record-title" name="title" class="input" required maxlength="120" placeholder="e.g. Annual booster, Bravecto, 4.2 kg"></div>
                </div>
                <div class="field__row field__row--three">
                    <div class="field"><label class="field__label" for="record-date">Date</label><input id="record-date" name="happened_on" type="date" class="input" value="{{ now()->toDateString() }}"></div>
                    <div class="field"><label class="field__label" for="record-due">Next due</label><input id="record-due" name="due_on" type="date" class="input"></div>
                    <div class="field"><label class="field__label" for="record-value">Weight or amount</label><input id="record-value" name="value" type="number" step="0.01" min="0" class="input"></div>
                </div>
                <div class="field"><label class="field__label" for="record-notes">Notes</label><input id="record-notes" name="notes" class="input" maxlength="1000"></div>
                @if($errors->any())<p class="field__error">{{ $errors->first() }}</p>@endif
                <button type="submit" class="button button--secondary">Add record</button>
            </form>
            @if($pet->records->isNotEmpty())
                <div class="data-list">
                    @foreach($pet->records as $record)
                        <article class="data-row">
                            <div class="data-row__main">
                                <p class="data-row__title">{{ $record->getAttribute('title') }}</p>
                                <div class="data-row__meta">
                                    <span class="badge">{{ $record->typeLabel() }}</span>
                                    @if($record->getAttribute('happened_on'))<span>{{ $record->getAttribute('happened_on')->isoFormat('D MMM YYYY') }}</span>@endif
                                    @if($record->getAttribute('value') !== null)<span>{{ rtrim(rtrim((string) $record->getAttribute('value'), '0'), '.') }}</span>@endif
                                    @if($record->getAttribute('due_on'))<span class="{{ $record->isOverdue() ? 'field__error' : '' }}">Next due {{ $record->getAttribute('due_on')->isoFormat('D MMM YYYY') }}</span>@endif
                                </div>
                                @if($record->getAttribute('notes'))<p class="text-muted">{{ $record->getAttribute('notes') }}</p>@endif
                            </div>
                            <form method="POST" action="{{ route('pettools.passport.records.destroy', [$pet->getKey(), $record->getKey()]) }}" class="data-row__actions">
                                @csrf @method('DELETE')
                                <button type="submit" class="button button--ghost button--small" aria-label="Remove record"><x-ui.icon name="trash"/></button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <aside class="stack">
        <section class="card"><div class="card__body stack stack--tight">
            <h2 class="card__title">Emergency QR pass</h2>
            @if($pet->getAttribute('pass_enabled') && $pet->getAttribute('pass_token'))
                @php $passUrl = route('pettools.pass', $pet->getAttribute('pass_token')); @endphp
                <div data-qr="{{ $passUrl }}" data-qr-label="{{ $pet->getAttribute('name') }}" style="width:200px;max-width:100%"></div>
                <p class="text-meta"><a class="text-link" href="{{ $passUrl }}" target="_blank" rel="noopener">Open the pass</a> · Right-click or press and hold the code to save it, then print it for the collar tag.</p>
            @else
                <p class="text-muted">Off. Switch it on to get a QR code that shows only what you tick below.</p>
            @endif
            <form method="POST" action="{{ route('pettools.passport.pass.update', $pet->getKey()) }}" class="stack stack--tight">
                @csrf
                @foreach($passFields as $key => $label)
                    <label class="row"><input type="checkbox" name="fields[]" value="{{ $key }}" @checked($pet->shows($key) || (! $pet->getAttribute('pass_token') && in_array($key, ['owner_phone', 'vet', 'medical'], true)))> <span>{{ $label }}</span></label>
                @endforeach
                <button type="submit" name="action" value="enable" class="button button--primary button--small">{{ $pet->getAttribute('pass_enabled') ? 'Save pass' : 'Switch on' }}</button>
                @if($pet->getAttribute('pass_enabled'))
                    <div class="row row--wrap">
                        <button type="submit" name="action" value="reset" class="button button--ghost button--small">New code</button>
                        <button type="submit" name="action" value="disable" class="button button--ghost button--small">Switch off</button>
                    </div>
                @endif
            </form>
        </div></section>

        <section class="card"><div class="card__body stack stack--tight">
            <h2 class="card__title">Missing?</h2>
            <form method="POST" action="{{ route('pettools.passport.missing', $pet->getKey()) }}">
                @csrf
                <button type="submit" class="button {{ $pet->getAttribute('is_missing') ? 'button--secondary' : 'button--critical' }} button--small">{{ $pet->getAttribute('is_missing') ? 'They are home' : 'Mark as missing' }}</button>
            </form>
        </div></section>

        <form method="POST" action="{{ route('pettools.passport.destroy', $pet->getKey()) }}" data-confirm="Remove {{ $pet->getAttribute('name') }} and all records?">
            @csrf @method('DELETE')
            <button type="submit" class="button button--ghost button--small">Remove this pet</button>
        </form>
    </aside>
</div>
@endsection
