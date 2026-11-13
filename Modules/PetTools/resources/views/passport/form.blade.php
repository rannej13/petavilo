@extends('panel::layouts.panel', ['panelSection' => 'pets'])

@section('title', $pet->exists ? 'Edit '.$pet->getAttribute('name') : 'Add a pet')

@php
    $value = static fn (string $key) => old($key, $pet->getAttribute($key) instanceof \Illuminate\Support\Carbon ? $pet->getAttribute($key)->toDateString() : $pet->getAttribute($key));
@endphp

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">{{ $pet->exists ? 'Edit '.$pet->getAttribute('name') : 'Add a pet' }}</h1>
        <p class="text-muted">Only the name and type are needed. Fill in the rest when you have it.</p>
    </div>
</header>

<form method="POST" enctype="multipart/form-data" action="{{ $pet->exists ? route('pettools.passport.update', $pet->getKey()) : route('pettools.passport.store') }}" class="stack">
    @csrf
    @if($pet->exists) @method('PUT') @endif

    <section class="card"><div class="card__body stack">
        <h2 class="card__title">About</h2>
        <div class="field__row field__row--two">
            <div class="field"><label class="field__label" for="name">Name</label><input id="name" name="name" class="input" required maxlength="60" value="{{ $value('name') }}">@error('name')<p class="field__error">{{ $message }}</p>@enderror</div>
            <div class="field"><label class="field__label" for="species">Type of pet</label>
                <select id="species" name="species" class="select" required>
                    @foreach(\Modules\PetTools\Models\Pet::speciesOptions() as $key => $label)<option value="{{ $key }}" @selected($value('species') === $key)>{{ $label }}</option>@endforeach
                </select></div>
        </div>
        <div class="field__row field__row--two">
            <div class="field"><label class="field__label" for="breed">Breed</label><input id="breed" name="breed" class="input" maxlength="80" value="{{ $value('breed') }}"></div>
            <div class="field"><label class="field__label" for="colour">Colour and markings</label><input id="colour" name="colour" class="input" maxlength="120" value="{{ $value('colour') }}"></div>
        </div>
        <div class="field__row field__row--three">
            <div class="field"><label class="field__label" for="sex">Sex</label><select id="sex" name="sex" class="select"><option value="">Not set</option>@foreach(['Male', 'Female', 'Unknown'] as $option)<option @selected($value('sex') === $option)>{{ $option }}</option>@endforeach</select></div>
            <div class="field"><label class="field__label" for="neutered">Neutered</label><select id="neutered" name="neutered" class="select"><option value="">Not set</option>@foreach(['Yes', 'No', 'Unknown'] as $option)<option @selected($value('neutered') === $option)>{{ $option }}</option>@endforeach</select></div>
            <div class="field"><label class="field__label" for="date_of_birth">Date of birth</label><input id="date_of_birth" name="date_of_birth" type="date" class="input" value="{{ $value('date_of_birth') }}"></div>
        </div>
        <div class="field"><label class="field__label" for="photo">Photo</label><input id="photo" name="photo" type="file" accept="image/*" class="input">@error('photo')<p class="field__error">{{ $message }}</p>@enderror</div>
    </div></section>

    <section class="card"><div class="card__body stack">
        <h2 class="card__title">Health</h2>
        <div class="field__row field__row--two">
            <div class="field"><label class="field__label" for="microchip_number">Microchip number</label><input id="microchip_number" name="microchip_number" class="input" maxlength="30" value="{{ old('microchip_number', $pet->getAttribute('microchip_number')) }}" autocomplete="off"><p class="field__hint">Stored encrypted. Never shown on the emergency pass.</p></div>
            <div class="field"><label class="field__label" for="microchip_database">Microchip database</label><input id="microchip_database" name="microchip_database" class="input" maxlength="80" placeholder="e.g. Petlog, Identibase, AKC Reunite" value="{{ $value('microchip_database') }}"></div>
        </div>
        <div class="field"><label class="field__label" for="allergies">Allergies</label><input id="allergies" name="allergies" class="input" maxlength="500" value="{{ $value('allergies') }}"></div>
        <div class="field"><label class="field__label" for="medications">Medicines</label><textarea id="medications" name="medications" class="textarea" rows="2" maxlength="1000">{{ $value('medications') }}</textarea></div>
        <div class="field"><label class="field__label" for="medical_notes">Medical notes</label><textarea id="medical_notes" name="medical_notes" class="textarea" rows="3" maxlength="2000" placeholder="Conditions, behaviour notes for a vet or carer">{{ $value('medical_notes') }}</textarea></div>
        <div class="field"><label class="field__label" for="diet">Diet</label><input id="diet" name="diet" class="input" maxlength="200" value="{{ $value('diet') }}"></div>
        <div class="field__row field__row--two">
            <div class="field"><label class="field__label" for="vet_name">Vet practice</label><input id="vet_name" name="vet_name" class="input" maxlength="120" value="{{ $value('vet_name') }}"></div>
            <div class="field"><label class="field__label" for="vet_phone">Vet phone</label><input id="vet_phone" name="vet_phone" type="tel" class="input" maxlength="40" value="{{ $value('vet_phone') }}"></div>
        </div>
    </div></section>

    <section class="card"><div class="card__body stack">
        <h2 class="card__title">Insurance</h2>
        <div class="field__row field__row--two">
            <div class="field"><label class="field__label" for="insurer">Insurer</label><input id="insurer" name="insurer" class="input" maxlength="120" value="{{ $value('insurer') }}"></div>
            <div class="field"><label class="field__label" for="policy_type">Policy type</label><select id="policy_type" name="policy_type" class="select"><option value="">Not set</option>@foreach(['Lifetime', 'Maximum benefit', 'Time limited', 'Accident only', 'Other'] as $option)<option @selected($value('policy_type') === $option)>{{ $option }}</option>@endforeach</select></div>
        </div>
        <div class="field__row field__row--two">
            <div class="field"><label class="field__label" for="policy_number">Policy number</label><input id="policy_number" name="policy_number" class="input" maxlength="60" autocomplete="off" value="{{ old('policy_number', $pet->getAttribute('policy_number')) }}"><p class="field__hint">Stored encrypted.</p></div>
            <div class="field"><label class="field__label" for="insurer_claims_phone">Claims phone</label><input id="insurer_claims_phone" name="insurer_claims_phone" type="tel" class="input" maxlength="40" value="{{ $value('insurer_claims_phone') }}"></div>
        </div>
        <div class="field__row field__row--three">
            <div class="field"><label class="field__label" for="policy_renewal">Renewal date</label><input id="policy_renewal" name="policy_renewal" type="date" class="input" value="{{ $value('policy_renewal') }}"></div>
            <div class="field"><label class="field__label" for="policy_excess">Excess</label><input id="policy_excess" name="policy_excess" type="number" step="0.01" min="0" class="input" value="{{ $value('policy_excess') }}"></div>
            <div class="field"><label class="field__label" for="policy_limit">Vet fee limit</label><input id="policy_limit" name="policy_limit" type="number" step="0.01" min="0" class="input" value="{{ $value('policy_limit') }}"></div>
        </div>
    </div></section>

    <section class="card"><div class="card__body stack">
        <h2 class="card__title">Contacts for the emergency pass</h2>
        <div class="field__row field__row--three">
            <div class="field"><label class="field__label" for="owner_phone">Your phone</label><input id="owner_phone" name="owner_phone" type="tel" class="input" maxlength="40" value="{{ $value('owner_phone') }}"></div>
            <div class="field"><label class="field__label" for="emergency_contact">Back-up contact name</label><input id="emergency_contact" name="emergency_contact" class="input" maxlength="120" value="{{ $value('emergency_contact') }}"></div>
            <div class="field"><label class="field__label" for="emergency_phone">Back-up contact phone</label><input id="emergency_phone" name="emergency_phone" type="tel" class="input" maxlength="40" value="{{ $value('emergency_phone') }}"></div>
        </div>
        <p class="field__hint">These only show on the pass if you switch it on and tick them.</p>
    </div></section>

    <div class="row row--between">
        <a href="{{ $pet->exists ? route('pettools.passport.show', $pet->getKey()) : route('pettools.passport.index') }}" class="button button--ghost">Cancel</a>
        <button type="submit" class="button button--primary">Save</button>
    </div>
</form>
@endsection
