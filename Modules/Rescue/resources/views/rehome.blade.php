@extends('site::layouts.app')

@section('title', 'Rehome your pet responsibly')
@section('description', 'Need to rehome a cat, dog, rabbit or other pet? Your options, from getting help to keep them to finding a verified rescue or posting a free rehoming advert.')

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        @include('rescue::partials.hub-nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Rehome responsibly</p>
            <h1 class="title-page">Need to rehome your pet?</h1>
            <p class="text-lead">It is a hard decision. Here are your options, in the order we suggest trying them.</p>
        </header>

        <ol class="stack">
            <li class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">1. Check whether the problem can be solved</h2>
                <p>Behaviour problems, allergies, a new baby or a landlord saying no can often be worked through. Talk to your vet, a qualified behaviourist or your landlord first. <a class="text-link" href="{{ route('directory.type', ['type' => 'behaviourists']) }}">Find a behaviourist</a></p>
            </div></li>
            <li class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">2. Contact the breeder or rescue you got them from</h2>
                <p>Good breeders and most rescues will take an animal back, and many ask you to in the adoption contract.</p>
            </div></li>
            <li class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">3. Ask a verified rescue to help</h2>
                <p>A rescue can assess your pet, find a carefully matched home and stay in touch after. Some have waiting lists, so ask early.</p>
                @if($rescues->isNotEmpty())
                    <ul class="stack stack--tight">
                        @foreach($rescues as $rescue)
                            <li><a class="text-link" href="{{ route('rescue.show', $rescue->getAttribute('slug')) }}">{{ $rescue->getAttribute('name') }}</a> <span class="text-meta">{{ $rescue->getAttribute('town') }} · {{ implode(', ', $rescue->speciesLabels()) }}</span></li>
                        @endforeach
                    </ul>
                @endif
                <p><a class="text-link" href="{{ route('rescue.rescues') }}">See all verified rescues</a> or <a class="text-link" href="{{ route('directory.type', ['type' => 'rescues']) }}">rescues in the directory</a>.</p>
            </div></li>
            <li class="card"><div class="card__body stack stack--tight">
                <h2 class="title-card">4. Post a free rehoming advert</h2>
                <p>Rehoming adverts are always free. Be honest about health and behaviour, choose Unknown when you are not sure, and meet adopters at your home. Ask for a small rehoming fee or a home visit to put off people who are not serious.</p>
                <p><a href="{{ route('panel.listings.create') }}" class="button button--primary">Post a free rehoming advert</a></p>
            </div></li>
        </ol>

        <aside class="notice">
            <p class="notice__title">Never</p>
            <p>Hand a pet to someone you have not met, use a courier, or give away a pet "free to good home" without checks. Pets given away free are sometimes resold or used for breeding.</p>
        </aside>
    </div>
</div>
@endsection
