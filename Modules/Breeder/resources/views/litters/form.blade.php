@extends('panel::layouts.panel', ['panelSection' => 'breeder'])

@section('title', $litter->exists ? 'Edit '.$litter->getAttribute('name') : 'New litter')

@php $v = static fn (string $key) => old($key, $litter->getAttribute($key) instanceof \Illuminate\Support\Carbon ? $litter->getAttribute($key)->toDateString() : $litter->getAttribute($key)); @endphp

@section('panel_content')
<header class="panel-head"><div class="panel-head__text">
    <p class="text-eyebrow"><a href="{{ route('breeder.dashboard') }}" class="text-link">Breeder tools</a></p>
    <h1 class="title-page">{{ $litter->exists ? 'Edit '.$litter->getAttribute('name') : 'New litter' }}</h1>
    <p class="text-muted">Leave the due date or go-home date blank and we'll suggest one from the mating or birth date.</p>
</div></header>

<form method="POST" action="{{ $litter->exists ? route('breeder.litters.update', $litter->getKey()) : route('breeder.litters.store') }}" class="card"><div class="card__body stack">
    @csrf
    @if($litter->exists) @method('PUT') @endif
    <div class="field__row field__row--three">
        <div class="field"><label class="field__label" for="name">Litter name</label><input id="name" name="name" class="input" required maxlength="80" value="{{ $v('name') }}" placeholder="e.g. Bella × Rocco, spring 2027">@error('name')<p class="field__error">{{ $message }}</p>@enderror</div>
        <div class="field"><label class="field__label" for="species">Species</label><select id="species" name="species" class="select">@foreach(\Modules\PetTools\Models\Pet::speciesOptions() as $key => $label)<option value="{{ $key }}" @selected($v('species') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label class="field__label" for="breed">Breed</label><input id="breed" name="breed" class="input" maxlength="80" value="{{ $v('breed') }}"></div>
    </div>
    <div class="field__row field__row--three">
        <div class="field"><label class="field__label" for="dam_id">Dam</label><select id="dam_id" name="dam_id" class="select"><option value="">Not recorded</option>@foreach($dams as $dam)<option value="{{ $dam->getKey() }}" @selected((string) $v('dam_id') === (string) $dam->getKey())>{{ $dam->getAttribute('name') }}</option>@endforeach</select>@if($dams->isEmpty())<p class="field__hint"><a class="text-link" href="{{ route('breeder.animals.create') }}">Add your dam first</a> for licence checks.</p>@endif</div>
        <div class="field"><label class="field__label" for="sire_id">Sire</label><select id="sire_id" name="sire_id" class="select"><option value="">Outside stud (type below)</option>@foreach($sires as $sire)<option value="{{ $sire->getKey() }}" @selected((string) $v('sire_id') === (string) $sire->getKey())>{{ $sire->getAttribute('name') }}</option>@endforeach</select></div>
        <div class="field"><label class="field__label" for="sire_name">Outside stud name</label><input id="sire_name" name="sire_name" class="input" maxlength="120" value="{{ $v('sire_name') }}"></div>
    </div>
    <div class="field__row field__row--three">
        <div class="field"><label class="field__label" for="mated_on">Mated on</label><input id="mated_on" name="mated_on" type="date" class="input" value="{{ $v('mated_on') }}"></div>
        <div class="field"><label class="field__label" for="due_on">Due</label><input id="due_on" name="due_on" type="date" class="input" value="{{ $v('due_on') }}"></div>
        <div class="field"><label class="field__label" for="born_on">Born</label><input id="born_on" name="born_on" type="date" class="input" value="{{ $v('born_on') }}"></div>
    </div>
    <div class="field__row field__row--three">
        <div class="field"><label class="field__label" for="born_count">Number born</label><input id="born_count" name="born_count" type="number" min="0" max="30" class="input" value="{{ $v('born_count') }}"></div>
        <div class="field"><label class="field__label" for="ready_on">Ready for new homes from</label><input id="ready_on" name="ready_on" type="date" class="input" value="{{ $v('ready_on') }}"></div>
        <div class="field"><label class="field__label" for="price">Price each</label><input id="price" name="price" type="number" step="0.01" min="0" class="input" value="{{ $v('price') }}"></div>
    </div>
    <div class="field__row field__row--two">
        <div class="field"><label class="field__label" for="status">Stage</label><select id="status" name="status" class="select">@foreach(\Modules\Breeder\Models\BreederLitter::STATUSES as $key => $label)<option value="{{ $key }}" @selected($v('status') === $key)>{{ $label }}</option>@endforeach</select></div>
        <label class="checkbox" style="align-self: end"><input type="checkbox" name="caesarean" value="1" @checked(old('caesarean', $litter->getAttribute('caesarean')))> <span>Delivered by caesarean</span></label>
    </div>
    <div class="field"><label class="field__label" for="notes">Notes</label><textarea id="notes" name="notes" class="textarea" rows="3" maxlength="3000">{{ $v('notes') }}</textarea></div>
    <div class="row"><button type="submit" class="button button--primary">Save litter</button></div>
</div></form>

@if($litter->exists)
    <form method="POST" action="{{ route('breeder.litters.destroy', $litter->getKey()) }}" class="row" data-confirm="Delete {{ $litter->getAttribute('name') }} with all its weights, records and handover links? This can't be undone.">
        @csrf @method('DELETE')
        <button type="submit" class="button button--ghost">Delete this litter</button>
    </form>
@endif
@endsection
