@extends('panel::layouts.panel', ['panelSection' => 'adoptions'])

@section('title', 'My donations')

@section('panel_content')
<header class="panel-head">
    <div class="panel-head__text"><h1 class="title-page">My donations</h1></div>
    <a href="{{ route('rescue.rescues') }}" class="button button--ghost">Verified rescues</a>
</header>

@if($donations->isEmpty())
    <x-ui.empty-state icon="heart" title="No donations yet" text="You can donate to any verified rescue from its page."/>
@else
    <section class="card"><div class="data-list">
        @foreach($donations as $donation)
            <article class="data-row"><div class="data-row__main">
                <p class="data-row__title">{{ $donation->totalLabel() }} to {{ $donation->organisation?->name }}</p>
                <div class="data-row__meta"><span>D{{ str_pad((string) $donation->getKey(), 6, '0', STR_PAD_LEFT) }}</span><span>{{ $donation->getAttribute('paid_at')?->isoFormat('D MMM YYYY') }}</span></div>
            </div></article>
        @endforeach
    </div></section>
@endif
@endsection
