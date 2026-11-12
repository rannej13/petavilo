<?php

declare(strict_types=1);

namespace Modules\Site\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::homeParentCategories();
        $featuredListings = Listing::homeFeatured();
        $recentListings = Listing::homeRecent();
        $listingCount = Listing::activeCount();
        $categoryCount = Category::activeCount();
        $userCount = User::totalCount();
        $heroStats = $this->heroStats($listingCount, $categoryCount, $userCount);
        $favoriteListingIds = auth()->check()
            ? auth()->user()->favoriteListingIds()
            : [];

        return view('site::home', compact(
            'categories',
            'featuredListings',
            'recentListings',
            'listingCount',
            'categoryCount',
            'userCount',
            'heroStats',
            'favoriteListingIds',
        ));
    }

    private function heroStats(int $listingCount, int $categoryCount, int $userCount): array
    {
        $stats = [];

        if ($listingCount >= 50) {
            $stats[] = [number_format($listingCount), __('site::messages.active_listings')];
        }

        $country = class_exists(\Modules\CatMarket\Support\CatMarketSettings::class)
            ? \Modules\CatMarket\Support\CatMarketSettings::visitorCountry()
            : 'GB';

        if (class_exists(\Modules\Directory\Models\Business::class)) {
            $businesses = array_sum(\Modules\Directory\Models\Business::countsByType($country));

            if ($businesses > 0) {
                $stats[] = [number_format($businesses), 'vets, rescues & cat services'];
            }
        }

        if (class_exists(\Modules\Guide\Models\Guide::class)) {
            $guides = \Modules\Guide\Models\Guide::query()->published()->count();

            if ($guides > 0) {
                $stats[] = [number_format($guides), 'free cat guides'];
            }
        }

        if ($userCount >= 500) {
            $stats[] = [number_format($userCount), __('site::messages.members')];
        }

        if (count($stats) < 2) {
            $stats[] = [number_format($categoryCount), __('site::messages.categories')];
        }

        return $stats;
    }
}
