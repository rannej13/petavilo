@extends('site::layouts.app')

@section('title', __('community::messages.seo_title'))
@section('description', __('community::messages.lead'))

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <header class="forum-hero">
            <div class="stack stack--tight">
                <p class="text-eyebrow">{{ __('community::messages.community') }}</p>
                <h1 class="title-page">{{ __('community::messages.seo_title') }}</h1>
                <p class="text-lead">{{ __('community::messages.lead') }}</p>
            </div>
            <a href="{{ route('community.create') }}" class="button button--primary">{{ __('community::messages.new_thread') }}</a>
        </header>

        <section class="stack">
            <h2 class="title-section">{{ __('community::messages.boards') }}</h2>
            <div class="grid grid--three">
                @foreach($boards as $board)
                    <a href="{{ route('community.board', $board->getAttribute('slug')) }}" class="card forum-board">
                        @if($image = $board->imageUrl())
                            <img src="{{ $image }}" alt="" class="card__image" loading="lazy" decoding="async">
                        @endif
                        <div class="card__body stack stack--tight">
                            <h3 class="title-card">{{ $board->getAttribute('name') }}</h3>
                            <p class="text-muted text-clamp-3">{{ $board->getAttribute('description') }}</p>
                            <p class="text-meta">{{ trans_choice('community::messages.threads', $board->getAttribute('threads_count'), ['count' => $board->getAttribute('threads_count')]) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <x-ads::slot name="community"/>

        <section class="stack">
            <h2 class="title-section">{{ __('community::messages.latest') }}</h2>
            @if($latest->isNotEmpty())
                <div class="card forum-list">
                    @foreach($latest as $thread)
                        @include('community::partials.thread-row', ['thread' => $thread, 'showBoard' => true])
                    @endforeach
                </div>
            @else
                <x-ui.empty-state icon="list" :title="__('community::messages.none')"/>
            @endif
        </section>

        <aside class="notice"><p class="notice__title">{{ __('community::messages.rules_title') }}</p><p>{{ __('community::messages.rules') }}</p></aside>
    </div>
</div>
@endsection
