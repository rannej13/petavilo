@extends('panel::layouts.panel', ['panelSection' => 'breeder'])

@section('title', 'Breeding register')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text">
        <p class="text-eyebrow no-print"><a href="{{ route('breeder.dashboard') }}" class="text-link">Breeder tools</a></p>
        <h1 class="title-page">Breeding register</h1>
        <p class="text-muted">Every litter born, with dams, microchips and new owners. Printed {{ now()->isoFormat('D MMMM YYYY') }}.</p>
    </div>
    <div class="row row--wrap no-print">
        <button type="button" class="button button--secondary" onclick="window.print()">Print</button>
        <a href="{{ route('breeder.register.csv') }}" class="button button--ghost">Download CSV</a>
    </div>
</header>

@forelse($litters as $litter)
    <section class="card">
        <div class="card__head"><h2 class="card__title">{{ $litter->getAttribute('name') }}</h2><span class="badge">{{ $litter->getAttribute('born_on')->isoFormat('D MMM YYYY') }}</span></div>
        <div class="card__body stack">
            <p class="text-meta">
                Dam: {{ $litter->dam?->getAttribute('name') ?? 'not recorded' }}@if($litter->dam?->getAttribute('date_of_birth')) (born {{ $litter->dam->getAttribute('date_of_birth')->isoFormat('D MMM YYYY') }})@endif
                · Sire: {{ $litter->sireLabel() }}
                @if($litter->getAttribute('mated_on')) · Mated {{ $litter->getAttribute('mated_on')->isoFormat('D MMM YYYY') }}@endif
                · {{ $litter->getAttribute('born_count') ?? $litter->offspring->count() }} born
                @if($litter->getAttribute('caesarean')) · Caesarean @endif
            </p>
            @if($litter->offspring->isNotEmpty())
                <div class="data-table-wrap"><table class="data-table">
                    <thead><tr><th>Name</th><th>Sex</th><th>Microchip</th><th>Status</th><th>New owner</th><th>Town</th><th>Went home</th></tr></thead>
                    <tbody>@foreach($litter->offspring as $o)<tr>
                        <td>{{ $o->getAttribute('name') }}</td>
                        <td>{{ ucfirst((string) $o->getAttribute('sex')) }}</td>
                        <td>{{ $o->getAttribute('microchip_number') }}</td>
                        <td>{{ \Modules\Breeder\Models\BreederOffspring::STATUSES[$o->getAttribute('status')] ?? '' }}</td>
                        <td>{{ $o->getAttribute('buyer_name') }}</td>
                        <td>{{ $o->getAttribute('buyer_town') }}</td>
                        <td>{{ $o->getAttribute('sold_at')?->isoFormat('D MMM YYYY') }}</td>
                    </tr>@endforeach</tbody>
                </table></div>
            @endif
        </div>
    </section>
@empty
    <x-ui.empty-state icon="heart" title="No litters with a birth date yet" text="Litters appear here once you add the date they were born."/>
@endforelse

<p class="text-muted" style="font-size: var(--text-xs)">Check your council's record-keeping requirements; this register is a helpful summary, not legal advice. <a class="text-link" href="{{ $source }}" target="_blank" rel="noopener">Licence conditions (England)</a>.</p>
@endsection
