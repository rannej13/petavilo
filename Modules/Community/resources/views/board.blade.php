@extends('site::layouts.app')

@section('title', $board->getAttribute('name').' · '.__('community::messages.community'))
@section('description', (string) $board->getAttribute('description'))

@section('content')
<div class="shell shell--wide page">
    <div class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('community.index') }}">{{ __('community::messages.community') }}</a>
        </nav>

        <header class="forum-hero">
            <div class="stack stack--tight">
                <h1 class="title-page">{{ $board->getAttribute('name') }}</h1>
                <p class="text-lead">{{ $board->getAttribute('description') }}</p>
            </div>
            <a href="{{ route('community.create', ['board' => $board->getKey()]) }}" class="button button--primary">{{ __('community::messages.new_thread') }}</a>
        </header>

        @if($threads->isNotEmpty())
            <div class="card forum-list">
                @foreach($threads as $thread)
                    @include('community::partials.thread-row', ['thread' => $thread])
                @endforeach
            </div>
            {{ $threads->links('components.pagination') }}
        @else
            <x-ui.empty-state icon="list" :title="__('community::messages.none')"/>
        @endif

        <x-ads::slot name="community"/>
    </div>
</div>
@endsection
