<?php

declare(strict_types=1);

namespace Modules\Tv\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Tv\Models\PetOfWeekEntry;
use Modules\Tv\Models\TvVideo;

class TvController extends Controller
{
    public function index(Request $request): View
    {
        $categories = TvVideo::categories();
        $category = (string) $request->query('c', '');
        $category = array_key_exists($category, $categories) ? $category : '';

        $videos = TvVideo::query()
            ->published()
            ->when($category !== '', static fn ($query) => $query->where('category', $category))
            ->limit(48)
            ->get();

        $source = strtolower((string) $request->query('utm_source', $request->query('from', '')));
        $greeting = match (true) {
            str_contains($source, 'tiktok') => 'Hi from TikTok!',
            str_contains($source, 'instagram'), str_contains($source, 'reels') => 'Hi from Instagram!',
            str_contains($source, 'youtube'), str_contains($source, 'shorts') => 'Hi from YouTube!',
            default => null,
        };

        $winner = PetOfWeekEntry::query()
            ->where('status', PetOfWeekEntry::STATUS_WINNER)
            ->where('consent_feature', true)
            ->latest('week_start')
            ->first();

        $usedCategories = TvVideo::query()->where('is_published', true)->distinct()->pluck('category')->all();

        return view('tv::index', [
            'videos' => $videos,
            'categories' => array_intersect_key($categories, array_flip($usedCategories)),
            'category' => $category,
            'greeting' => $greeting,
            'winner' => $winner,
        ]);
    }

    public function played(TvVideo $video): JsonResponse
    {
        if ($video->getAttribute('is_published')) {
            $video->increment('plays');
        }

        return response()->json(['ok' => true]);
    }
}
