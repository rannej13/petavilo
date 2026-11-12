<?php

declare(strict_types=1);

namespace Modules\Community\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\Community\Models\Board;
use Modules\Community\Models\Community;
use Modules\Community\Models\Reply;
use Modules\Community\Models\Thread;

class CommunityController extends Controller
{
    public function index(): View
    {
        $boards = Board::query()->active()
            ->withCount(['threads' => static fn ($query) => $query->visible()])
            ->get();

        $latest = Thread::query()->visible()
            ->whereHas('board', static fn ($query) => $query->where('is_active', true))
            ->with(['board', 'user'])
            ->latest('last_post_at')
            ->limit(12)
            ->get();

        return view('community::index', ['boards' => $boards, 'latest' => $latest]);
    }

    public function board(string $slug): View
    {
        $board = Board::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $threads = $board->threads()->visible()
            ->with('user')
            ->orderByDesc('is_pinned')
            ->latest('last_post_at')
            ->paginate(25);

        return view('community::board', ['board' => $board, 'threads' => $threads]);
    }

    public function thread(Request $request, int $thread, ?string $slug = null): View|RedirectResponse
    {
        $record = Thread::query()->with(['board', 'user'])->findOrFail($thread);
        $isStaff = Community::isStaff($request->user());

        abort_if($record->getAttribute('is_hidden') && ! $isStaff, 404);

        if ($slug !== $record->getAttribute('slug')) {
            return redirect()->to($record->url(), 301);
        }

        $key = 'community-view:'.$record->getKey().':'.sha1((string) $request->ip());

        if (Cache::add($key, true, now()->addHours(6))) {
            $record->newQuery()->whereKey($record->getKey())->increment('view_count');
        }

        $replies = $record->replies()
            ->when(! $isStaff, static fn ($query) => $query->where('is_hidden', false))
            ->with('user')
            ->oldest()
            ->paginate(30);

        return view('community::thread', ['thread' => $record, 'replies' => $replies]);
    }

    public function create(Request $request): View
    {
        $boards = Board::query()->active()->get();
        $selected = (string) $request->query('board', '');

        return view('community::create', ['boards' => $boards, 'selected' => $selected]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'board_id' => ['required', 'integer', 'exists:community_boards,id'],
            'title' => ['required', 'string', 'min:6', 'max:160'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
        ]);

        $thread = Thread::query()->create($data + [
            'user_id' => $request->user()->getKey(),
            'country' => CatMarketSettings::visitorCountry(),
        ]);

        return redirect()->to($thread->url());
    }

    public function reply(Request $request, int $thread): RedirectResponse
    {
        $record = Thread::query()->visible()->findOrFail($thread);

        if ($record->getAttribute('is_locked') && ! Community::isStaff($request->user())) {
            return back()->with('error', __('community::messages.locked'));
        }

        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:10000']]);

        $reply = Reply::query()->create([
            'thread_id' => $record->getKey(),
            'user_id' => $request->user()->getKey(),
            'body' => $data['body'],
        ]);

        $page = (int) ceil(max(1, $record->replies()->where('is_hidden', false)->count()) / 30);

        return redirect()->to($record->url().'?page='.$page.'#reply-'.$reply->getKey());
    }

    public function report(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:thread,reply'],
            'id' => ['required', 'integer'],
        ]);

        $key = 'community-report:'.$data['type'].':'.$data['id'].':'.$request->user()->getKey();

        if (Cache::add($key, true, now()->addDays(30))) {
            $model = $data['type'] === 'thread' ? Thread::query() : Reply::query();
            $model->whereKey($data['id'])->increment('report_count');
        }

        return back()->with('success', __('community::messages.reported'));
    }
}
