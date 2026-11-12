@php
    $author = $post->authorName();
    $isTeam = \Modules\Community\Models\Community::isStaff($post->user);
@endphp
<article class="card forum-post {{ $post->getAttribute('is_hidden') ? 'is-hidden' : '' }}" id="{{ $type }}-{{ $post->getKey() }}">
    <div class="card__body stack stack--tight">
        <div class="forum-post__head">
            <span class="forum-avatar {{ $isTeam ? 'forum-avatar--team' : '' }}" aria-hidden="true">{{ \Modules\Community\Models\Community::initials($author) }}</span>
            <span class="stack stack--tight" style="gap:0">
                <strong>{{ $author }} @if($isTeam)<span class="badge badge--solid">{{ __('community::messages.staff') }}</span>@endif</strong>
                <time class="text-meta" datetime="{{ $post->getAttribute('created_at')?->toIso8601String() }}">{{ $post->getAttribute('created_at')?->diffForHumans() }}</time>
            </span>
            @auth
                @if((int) auth()->id() !== (int) $post->getAttribute('user_id'))
                    <form method="POST" action="{{ route('community.report') }}" class="forum-post__report">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="id" value="{{ $post->getKey() }}">
                        <button type="submit" class="button button--ghost button--small">{{ __('community::messages.report') }}</button>
                    </form>
                @endif
            @endauth
        </div>
        @if($post->getAttribute('is_hidden'))<p class="text-meta">{{ __('community::messages.hidden') }}</p>@endif
        <div class="prose forum-post__body">{{ \Modules\Community\Models\Community::format((string) $post->getAttribute('body')) }}</div>
    </div>
</article>
