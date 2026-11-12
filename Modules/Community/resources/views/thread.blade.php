@extends('site::layouts.app')

@section('title', $thread->getAttribute('title').' · '.__('community::messages.community'))
@section('description', \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $thread->getAttribute('body')), 155))

@php
    $isStaffViewer = \Modules\Community\Models\Community::isStaff(auth()->user());
@endphp

@section('content')
<div class="shell shell--narrow page">
    <div class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('community.index') }}">{{ __('community::messages.community') }}</a>
            <span class="breadcrumb__separator">/</span>
            <a href="{{ route('community.board', $thread->board->getAttribute('slug')) }}">{{ $thread->board->getAttribute('name') }}</a>
        </nav>

        <header class="stack stack--tight">
            <h1 class="title-page">{{ $thread->getAttribute('title') }}</h1>
            <p class="text-meta">
                {{ trans_choice('community::messages.replies', $thread->getAttribute('reply_count'), ['count' => $thread->getAttribute('reply_count')]) }}
                · {{ __('community::messages.views', ['count' => $thread->getAttribute('view_count')]) }}
                @if($thread->getAttribute('is_hidden')) · <span class="badge badge--critical">{{ __('community::messages.hidden') }}</span>@endif
            </p>
        </header>

        @if(session('success'))<div class="notice notice--accent"><p>{{ session('success') }}</p></div>@endif
        @if(session('error'))<div class="notice notice--caution"><p>{{ session('error') }}</p></div>@endif

        @if($replies->currentPage() === 1)
            @include('community::partials.post', ['post' => $thread, 'type' => 'thread', 'isStaffViewer' => $isStaffViewer])

            @if(in_array($thread->board->getAttribute('slug'), ['health-and-behaviour', 'kittens-and-new-cats'], true))
                <aside class="notice notice--caution" role="note"><p>{{ __('community::messages.vet_notice') }}</p></aside>
            @endif

            <x-ads::slot name="community"/>
        @endif

        @foreach($replies as $reply)
            @include('community::partials.post', ['post' => $reply, 'type' => 'reply', 'isStaffViewer' => $isStaffViewer])
        @endforeach

        {{ $replies->links('components.pagination') }}

        @if($thread->getAttribute('is_locked') && ! $isStaffViewer)
            <div class="notice"><p>{{ __('community::messages.locked') }}</p></div>
        @elseauth
            <form method="POST" action="{{ route('community.reply', $thread->getKey()) }}" class="card" id="reply">
                @csrf
            {{ \App\Support\SpamGuard::fields() }}
                <div class="card__body stack">
                    <div class="field">
                        <label class="field__label" for="reply-body">{{ __('community::messages.reply') }}</label>
                        <textarea id="reply-body" name="body" class="textarea" rows="5" required minlength="2" maxlength="10000">{{ old('body') }}</textarea>
                        @error('body')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="button button--primary">{{ __('community::messages.post_reply') }}</button>
                </div>
            </form>
        @else
            <a href="{{ route('login') }}" class="button button--primary">{{ __('community::messages.login_to_reply') }}</a>
        @endauth
    </div>
</div>
@endsection
