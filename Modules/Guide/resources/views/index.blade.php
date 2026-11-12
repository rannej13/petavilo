@extends('site::layouts.app')

@section('title', __('guide::messages.guides'))
@section('description', __('guide::messages.guides_lead'))

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <header class="stack stack--tight">
            <h1 class="title-page">{{ __('guide::messages.guides') }}</h1>
            <p class="text-lead">{{ __('guide::messages.guides_lead') }}</p>
        </header>

        <div class="chip-nav">
            <a href="{{ route('guides.index') }}" class="pill {{ $type === null ? 'is-active' : '' }}">{{ __('guide::messages.all') }}</a>
            @foreach($types as $slug => $label)
                <a href="{{ route('guides.index', ['type' => $slug]) }}" class="pill {{ $type === $slug ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($guides->isNotEmpty())
            <div class="grid grid--three">
                @foreach($guides as $guide)
                    <a href="{{ route('guides.show', $guide->getAttribute('slug')) }}" class="card">
                        <div class="card__body stack stack--tight">
                            <p class="text-eyebrow">{{ $guide->typeLabel() }}</p>
                            <h2 class="title-card">{{ $guide->getAttribute('title') }}</h2>
                            @if(filled($guide->getAttribute('excerpt')))
                                <p class="text-muted text-clamp-3">{{ $guide->getAttribute('excerpt') }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            {{ $guides->links('components.pagination') }}
        @else
            <x-ui.empty-state icon="list" :title="__('guide::messages.none')"/>
        @endif
    </div>
</div>
@endsection
