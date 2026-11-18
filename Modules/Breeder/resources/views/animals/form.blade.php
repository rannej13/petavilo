@extends('panel::layouts.panel', ['panelSection' => 'breeder'])

@section('title', $animal->exists ? 'Edit '.$animal->getAttribute('name') : 'Add a dam or sire')

@php $v = static fn (string $key) => old($key, $animal->getAttribute($key) instanceof \Illuminate\Support\Carbon ? $animal->getAttribute($key)->toDateString() : $animal->getAttribute($key)); @endphp

@section('panel_content')
<header class="panel-head"><div class="panel-head__text">
    <p class="text-eyebrow"><a href="{{ route('breeder.dashboard') }}" class="text-link">Breeder tools</a></p>
    <h1 class="title-page">{{ $animal->exists ? 'Edit '.$animal->getAttribute('name') : 'Add a dam or sire' }}</h1>
</div></header>

<form method="POST" action="{{ $animal->exists ? route('breeder.animals.update', $animal->getKey()) : route('breeder.animals.store') }}" class="card"><div class="card__body stack">
    @csrf
    @if($animal->exists) @method('PUT') @endif
    <div class="field__row field__row--three">
        <div class="field"><label class="field__label" for="name">Name</label><input id="name" name="name" class="input" required maxlength="80" value="{{ $v('name') }}">@error('name')<p class="field__error">{{ $message }}</p>@enderror</div>
        <div class="field"><label class="field__label" for="species">Species</label><select id="species" name="species" class="select">@foreach(\Modules\PetTools\Models\Pet::speciesOptions() as $key => $label)<option value="{{ $key }}" @selected($v('species') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label class="field__label" for="sex">Role</label><select id="sex" name="sex" class="select"><option value="female" @selected($v('sex') === 'female')>Dam (female)</option><option value="male" @selected($v('sex') === 'male')>Sire (male)</option></select></div>
    </div>
    <div class="field__row field__row--three">
        <div class="field"><label class="field__label" for="breed">Breed</label><input id="breed" name="breed" class="input" maxlength="80" value="{{ $v('breed') }}"></div>
        <div class="field"><label class="field__label" for="date_of_birth">Date of birth</label><input id="date_of_birth" name="date_of_birth" type="date" class="input" value="{{ $v('date_of_birth') }}"><p class="field__hint">Needed for the licence age checks.</p></div>
        <div class="field"><label class="field__label" for="registration">Registration number</label><input id="registration" name="registration" class="input" maxlength="80" value="{{ $v('registration') }}" placeholder="e.g. Kennel Club, GCCF"></div>
    </div>
    <div class="field"><label class="field__label" for="microchip_number">Microchip number</label><input id="microchip_number" name="microchip_number" class="input" maxlength="30" autocomplete="off" value="{{ old('microchip_number', $animal->getAttribute('microchip_number')) }}"><p class="field__hint">Stored encrypted. Shown only to you and in your register.</p></div>
    <div class="field"><label class="field__label" for="health_tests">Health tests and results</label><textarea id="health_tests" name="health_tests" class="textarea" rows="2" maxlength="1000" placeholder="e.g. Hips 4:5 (BVA), eyes clear 2026, PRA DNA clear">{{ $v('health_tests') }}</textarea><p class="field__hint">Used in your advert text, so write it as buyers should see it.</p></div>
    <div class="field"><label class="field__label" for="notes">Private notes</label><textarea id="notes" name="notes" class="textarea" rows="2" maxlength="2000">{{ $v('notes') }}</textarea></div>
    @if($animal->exists)<label class="checkbox"><input type="checkbox" name="retired" value="1" @checked(! $animal->getAttribute('is_active'))> <span>Retired from breeding</span></label>@endif
    <div class="row"><button type="submit" class="button button--primary">Save</button></div>
</div></form>
@endsection
