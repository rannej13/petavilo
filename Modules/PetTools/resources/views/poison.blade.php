@extends('site::layouts.app')

@section('title', 'Is it poisonous to my dog, cat, rabbit or bird?')
@section('description', 'Check whether a food, plant, medicine or household product is poisonous to dogs, cats, rabbits and birds, and who to call if your pet has eaten it.')

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        @include('pettools::partials.nav')

        <header class="stack stack--tight">
            <p class="text-eyebrow">Poison checker</p>
            <h1 class="title-page">Is it poisonous to my pet?</h1>
        </header>

        <div class="notice notice--caution" role="note">
            <p class="notice__title">Has your pet already eaten something worrying?</p>
            <p>Do not wait for signs and do not try to make them sick. Call your vet now. Have the packet with you.</p>
            @if($country === 'US')
                <p>ASPCA Animal Poison Control: <a class="text-link" href="tel:8884264435">(888) 426-4435</a> · Pet Poison Helpline: <a class="text-link" href="tel:8002136680">(800) 213-6680</a>. Both are open 24 hours and charge a fee.</p>
            @else
                <p>Animal PoisonLine, 24 hours: <a class="text-link" href="tel:01202509000">01202 509000</a>. A fee applies, and many insurance policies cover it.</p>
            @endif
        </div>

        <form method="GET" class="stack stack--tight" role="search">
            <div class="field">
                <label class="field__label" for="q">What did they eat or touch?</label>
                <span class="input-affix">
                    <x-ui.icon name="search" class="input-affix__icon"/>
                    <input id="q" name="q" type="search" class="input" value="{{ $query }}" placeholder="e.g. grapes, lily, ibuprofen, chocolate" autofocus>
                </span>
            </div>
            <div class="field__row field__row--two">
                <div class="field">
                    <label class="field__label" for="pet">Pet</label>
                    <select id="pet" name="pet" class="select">
                        <option value="">Any pet</option>
                        @foreach($speciesOptions as $key => $label)<option value="{{ $key }}" @selected($species === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="type">Type</label>
                    <select id="type" name="type" class="select">
                        <option value="">Everything</option>
                        @foreach($categories as $key => $label)<option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="button button--primary">Check</button>
        </form>

        @if($items === [])
            <x-ui.empty-state icon="search" title="Not in our list" text="That does not mean it is safe. If your pet has eaten it, call your vet or a poison line."/>
        @else
            <div class="stack">
                @foreach($items as $item)
                    <article class="card" id="{{ $item['slug'] }}"><div class="card__body stack stack--tight">
                        <h2 class="title-card">{{ $item['name'] }}</h2>
                        <div class="row row--wrap">
                            @foreach($speciesOptions as $key => $label)
                                @if(isset($item['risk'][$key]) && ($species === null || $species === $key))
                                    @php $level = $levels[$item['risk'][$key]]; @endphp
                                    <span class="badge {{ $level['tone'] }}" title="{{ $level['advice'] }}">{{ $label }}: {{ $level['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                        <p class="text-muted">{{ $item['note'] }}</p>
                        @php $worst = collect($item['risk'])->only($species === null ? array_keys($item['risk']) : [$species])->values(); @endphp
                        @if($worst->contains('danger'))
                            <p><strong>{{ $levels['danger']['advice'] }}</strong></p>
                        @elseif($worst->contains('toxic'))
                            <p><strong>{{ $levels['toxic']['advice'] }}</strong></p>
                        @endif
                    </div></article>
                @endforeach
            </div>
        @endif

        <p class="text-meta">General information drawn from guidance by veterinary poison services and animal charities. Not a substitute for advice from a vet. Reptiles and fish are not covered.</p>
    </div>
</div>
@endsection
