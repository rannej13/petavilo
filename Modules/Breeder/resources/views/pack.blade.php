@extends('site::layouts.app')

@section('title', $offspring->getAttribute('name').': handover pack')

@php
    $records = $offspring->records;
    $weights = $records->where('kind', 'weight');
    $health = $records->where('kind', '!=', 'weight');
    $species = \Modules\PetTools\Models\Pet::speciesOptions()[$litter->getAttribute('species')] ?? 'Pet';
    $link = route('breeder.handover', $offspring->getAttribute('handover_token'));
@endphp

@section('content')
<div class="shell page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <p class="text-eyebrow">Handover pack</p>
            <h1 class="title-page">{{ $offspring->getAttribute('name') }}</h1>
            <p class="text-lead">{{ $litter->getAttribute('breed') ?: $species }} · {{ ucfirst((string) $offspring->getAttribute('sex')) }}@if($litter->getAttribute('born_on')) · born {{ $litter->getAttribute('born_on')->isoFormat('D MMMM YYYY') }}@endif</p>
        </header>

        @if($forBreeder)
            <section class="card no-print"><div class="card__body grid grid--split" style="align-items: center">
                <div class="stack stack--tight">
                    <h2 class="card__title">Give this to {{ $offspring->getAttribute('buyer_name') ?: 'the new owner' }}</h2>
                    <p class="text-muted">They scan the code or open the link, sign in, and {{ $offspring->getAttribute('name') }} is added to their free Pet Passport with every record below.</p>
                    <input class="input" readonly value="{{ $link }}" onclick="this.select()" aria-label="Handover link">
                    <div class="row row--wrap"><button type="button" class="button button--secondary" onclick="window.print()">Print this pack</button></div>
                    @if($offspring->getAttribute('handover_claimed_at'))<p class="badge badge--positive">Added to their Pet Passport on {{ $offspring->getAttribute('handover_claimed_at')->isoFormat('D MMM YYYY') }}</p>@endif
                </div>
                <div data-qr="{{ $link }}" data-qr-label="{{ $offspring->getAttribute('name') }}'s handover pack" style="max-width: 200px"></div>
            </div></section>
        @else
            <section class="card no-print"><div class="card__body stack">
                @if($offspring->getAttribute('handover_claimed_at'))
                    <p>{{ $offspring->getAttribute('name') }} has already been added to a Pet Passport.</p>
                @else
                    <h2 class="card__title">Add {{ $offspring->getAttribute('name') }} to your free Pet Passport</h2>
                    <p class="text-muted">Keeps these records, reminds you when vaccinations are due, and gives you an emergency QR pass. Free, with no adverts inside.</p>
                    @auth
                        <form method="POST" action="{{ route('breeder.handover.claim', $offspring->getAttribute('handover_token')) }}" class="row row--wrap">
                            @csrf
                            <label class="visually-hidden" for="pet-name">Your pet's name</label>
                            <input id="pet-name" name="name" class="input" required maxlength="60" value="{{ preg_replace('/^\d+\.\s*/', '', (string) $offspring->getAttribute('name')) }}" style="max-width: 16rem">
                            <button type="submit" class="button button--primary">Add to my Pet Passport</button>
                        </form>
                    @else
                        <div class="row row--wrap"><a href="{{ route('login', ['redirect' => $link]) }}" class="button button--primary">Sign in to add</a><a href="{{ route('register', ['redirect' => $link]) }}" class="button button--secondary">Create a free account</a></div>
                    @endauth
                @endif
            </div></section>
        @endif

        <section class="card"><div class="card__body stack">
            <h2 class="card__title">Details</h2>
            <ul class="welfare-list">
                <li class="welfare-list__item"><span>Colour and markings</span><span>{{ $offspring->getAttribute('colour') ?: 'Not recorded' }}</span></li>
                <li class="welfare-list__item"><span>Microchip</span><span>{{ $forBreeder ? ($offspring->getAttribute('microchip_number') ?: 'Not recorded') : (filled($offspring->getAttribute('microchip_number')) ? 'Recorded (shown in your Pet Passport)' : 'Not recorded') }}</span></li>
                <li class="welfare-list__item"><span>Mother</span><span>{{ $litter->dam?->getAttribute('name') ?? 'Not recorded' }}</span></li>
                <li class="welfare-list__item"><span>Father</span><span>{{ $litter->sireLabel() }}</span></li>
                @if($litter->dam?->getAttribute('health_tests'))<li class="welfare-list__item"><span>Parent health tests</span><span>{{ $litter->dam->getAttribute('health_tests') }}</span></li>@endif
            </ul>
            @if($offspring->getAttribute('care_notes'))<p><strong>From the breeder:</strong> {{ $offspring->getAttribute('care_notes') }}</p>@endif
        </div></section>

        <section class="card"><div class="card__body stack">
            <h2 class="card__title">Health records</h2>
            @if($health->isEmpty())<p class="text-muted">None recorded.</p>@else
                <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Date</th><th>What</th><th>Notes</th></tr></thead><tbody>
                    @foreach($health as $record)<tr><td>{{ $record->getAttribute('recorded_on')->isoFormat('D MMM YYYY') }}</td><td>{{ $record->label() }}</td><td style="white-space: normal">{{ $record->getAttribute('details') }}</td></tr>@endforeach
                </tbody></table></div>
            @endif
        </div></section>

        <section class="card"><div class="card__body stack">
            <h2 class="card__title">Weight</h2>
            @if($weights->isEmpty())<p class="text-muted">None recorded.</p>@else
                <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Date</th><th>Weight</th><th>Age</th></tr></thead><tbody>
                    @foreach($weights as $record)<tr><td>{{ $record->getAttribute('recorded_on')->isoFormat('D MMM YYYY') }}</td><td>{{ number_format((int) $record->getAttribute('grams')) }} g</td><td>@if($litter->getAttribute('born_on')){{ (int) $litter->getAttribute('born_on')->diffInDays($record->getAttribute('recorded_on')) }} days @endif</td></tr>@endforeach
                </tbody></table></div>
            @endif
        </div></section>

        <p class="text-muted" style="font-size: var(--text-xs)">Records entered by the breeder on Petavilo. Ask your own vet to check them at your first visit.</p>
    </div>
</div>
@endsection
