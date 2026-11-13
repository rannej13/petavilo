@extends('panel::layouts.panel', ['panelSection' => 'pets'])

@section('title', 'My pets')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <h1 class="title-page">My pets</h1>
        <p class="text-muted">Health records, insurance and emergency passes.</p>
    </div>
    <a href="{{ route('pettools.passport.create') }}" class="button button--primary"><x-ui.icon name="plus"/><span>Add a pet</span></a>
</header>

@if($pets->isEmpty())
    <x-ui.empty-state icon="heart" title="Add your first pet" text="Keep vaccinations, treatments, weight, vet and insurance details together, with reminders.">
        <a href="{{ route('pettools.passport.create') }}" class="button button--primary">Add a pet</a>
    </x-ui.empty-state>
@else
    <div class="grid grid--three">
        @foreach($pets as $pet)
            @php $next = $pet->dueRecords()->first(); @endphp
            <a href="{{ route('pettools.passport.show', $pet->getKey()) }}" class="card">
                @if($photo = $pet->photoUrl())<img src="{{ $photo }}" alt="" class="card__image" loading="lazy">@endif
                <div class="card__body stack stack--tight">
                    <div class="row row--between">
                        <h2 class="title-card">{{ $pet->getAttribute('name') }}</h2>
                        @if($pet->getAttribute('is_missing'))<span class="badge badge--critical">Missing</span>@endif
                    </div>
                    <p class="text-meta">{{ $pet->speciesLabel() }}@if($pet->getAttribute('breed')) · {{ $pet->getAttribute('breed') }}@endif @if($age = $pet->ageLabel()) · {{ $age }}@endif</p>
                    @if($next)
                        <p class="{{ $next->isOverdue() ? 'field__error' : 'text-muted' }}">{{ $next->getAttribute('title') }} {{ $next->isOverdue() ? 'was due' : 'due' }} {{ $next->getAttribute('due_on')->isoFormat('D MMM YYYY') }}</p>
                    @endif
                    @if($pet->getAttribute('policy_renewal'))
                        <p class="text-meta">Insurance renews {{ $pet->getAttribute('policy_renewal')->isoFormat('D MMM YYYY') }}</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif

<aside class="notice">
    <p class="notice__title">Free tools</p>
    <p><a class="text-link" href="{{ route('pettools.poison') }}">Is it poisonous?</a> · <a class="text-link" href="{{ route('pettools.food') }}">Food matcher</a> · <a class="text-link" href="{{ route('rescue.found-animal') }}">Emergency numbers</a></p>
</aside>
@endsection
