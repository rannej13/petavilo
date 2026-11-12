<a href="{{ $thread->url() }}" class="forum-row">
    <span class="forum-avatar" aria-hidden="true">{{ \Modules\Community\Models\Community::initials($thread->authorName()) }}</span>
    <span class="forum-row__main">
        <span class="forum-row__title">
            @if($thread->getAttribute('is_pinned'))<span class="badge badge--solid">{{ __('community::messages.pinned') }}</span>@endif
            {{ $thread->getAttribute('title') }}
        </span>
        <span class="text-meta">
            {{ $thread->authorName() }}
            @if($showBoard ?? false) · {{ $thread->board?->getAttribute('name') }}@endif
            @if(filled($thread->getAttribute('country'))) · {{ $thread->getAttribute('country') === 'US' ? __('community::messages.region_us') : __('community::messages.region_uk') }}@endif
            · {{ __('community::messages.last_post', ['time' => $thread->getAttribute('last_post_at')?->diffForHumans()]) }}
        </span>
    </span>
    <span class="forum-row__count">
        <strong>{{ $thread->getAttribute('reply_count') }}</strong>
        <span class="text-meta">{{ trans_choice('community::messages.reply_word', $thread->getAttribute('reply_count')) }}</span>
    </span>
</a>
