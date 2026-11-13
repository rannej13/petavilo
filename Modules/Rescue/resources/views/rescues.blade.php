@extends('site::layouts.app')

@section('title', 'Verified pet rescues and charities')
@section('description', 'Rescues and animal charities we have checked, for cats, dogs, rabbits and other pets in the UK and US.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <header class="row row--between row--wrap">
            <div class="stack stack--tight">
                <p class="text-eyebrow">Rescues</p>
                <h1 class="title-page">Verified rescues and charities</h1>
                <p class="text-lead">We check charity registration or nonprofit status before a rescue gets the Verified badge.</p>
            </div>
            @if($mine)
                <a href="{{ route('rescue.panel') }}" class="button button--secondary">Your rescue dashboard</a>
            @else
                <a href="{{ route('rescue.join') }}" class="button button--primary">Add your rescue, free</a>
            @endif
        </header>

        @include('rescue::partials.species-filter')

        @if($rescues->isNotEmpty())
            <div class="stack">
                @foreach($rescues as $rescue)
                    @include('rescue::partials.rescue-row', ['rescue' => $rescue])
                @endforeach
            </div>
            {{ $rescues->links('components.pagination') }}
        @else
            <x-ui.empty-state icon="shield" title="No verified rescues here yet" text="We are checking rescues now. In the meantime, the directory lists rescues and charities near you.">
                <a href="{{ route('directory.type', ['type' => 'rescues']) }}" class="button button--secondary">Rescues in the directory</a>
            </x-ui.empty-state>
        @endif
    </div>
</div>
@endsection
