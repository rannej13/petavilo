@extends('site::layouts.app')

@section('title', __('community::messages.new_thread'))

@section('content')
<div class="shell shell--form page">
    <div class="stack stack--loose">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('community.index') }}">{{ __('community::messages.community') }}</a>
        </nav>
        <h1 class="title-page">{{ __('community::messages.new_thread') }}</h1>

        <form method="POST" action="{{ route('community.store') }}" class="card">
            @csrf
            {{ \App\Support\SpamGuard::fields() }}
            <div class="card__body stack">
                <div class="field">
                    <label class="field__label" for="t-board">{{ __('community::messages.board') }}</label>
                    <select id="t-board" name="board_id" class="select" required>
                        @foreach($boards as $board)
                            <option value="{{ $board->getKey() }}" @selected((string) old('board_id', $selected) === (string) $board->getKey())>{{ $board->getAttribute('name') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="field__label" for="t-title">{{ __('community::messages.title') }}</label>
                    <input id="t-title" name="title" class="input" required minlength="6" maxlength="160" value="{{ old('title') }}">
                    <p class="field__hint">{{ __('community::messages.title_hint') }}</p>
                    @error('title')<p class="field__error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label class="field__label" for="t-body">{{ __('community::messages.body') }}</label>
                    <textarea id="t-body" name="body" class="textarea" rows="8" required minlength="10" maxlength="10000">{{ old('body') }}</textarea>
                    <p class="field__hint">{{ __('community::messages.body_hint') }}</p>
                    @error('body')<p class="field__error">{{ $message }}</p>@enderror
                </div>
                <p class="text-meta">{{ __('community::messages.rules') }}</p>
                <button type="submit" class="button button--primary">{{ __('community::messages.post') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
