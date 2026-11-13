@extends('site::layouts.app')

@section('title', 'Foster a pet for a rescue')
@section('description', 'Become a foster carer for a verified rescue near you. Fosterers give cats, dogs and other animals a home while they wait for adoption.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Foster</p>
            <h1 class="title-page">Foster a pet for a rescue</h1>
            <p class="text-lead">Fosterers care for an animal at home until it is adopted. The rescue usually covers food, vet bills and equipment, and supports you throughout.</p>
        </header>

        <section class="grid grid--three">
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">Who can foster</h2><p class="text-muted">People with time at home, a safe space and patience. Renters need permission from their landlord.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">What it involves</h2><p class="text-muted">Feeding, settling in, vet trips and telling the rescue how the animal is doing. Stays range from days to months.</p></div></div>
            <div class="card"><div class="card__body stack stack--tight"><h2 class="title-card">How to start</h2><p class="text-muted">Contact a rescue below. They will check your home and match you with an animal that suits you.</p></div></div>
        </section>

        <section class="stack">
            <h2 class="title-section">Rescues looking for fosterers</h2>
            @if($rescues->isNotEmpty())
                <div class="stack">
                    @foreach($rescues as $rescue)
                        @include('rescue::partials.rescue-row', ['rescue' => $rescue])
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="users" title="No rescues listed here yet" text="Verified rescues that need fosterers will appear here.">
                    <a href="{{ route('directory.type', ['type' => 'rescues']) }}" class="button button--secondary">Rescues in the directory</a>
                </x-ui.empty-state>
            @endif
        </section>
    </div>
</div>
@endsection
