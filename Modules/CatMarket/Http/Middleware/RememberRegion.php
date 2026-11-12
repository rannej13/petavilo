<?php

declare(strict_types=1);

namespace Modules\CatMarket\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class RememberRegion
{
    public function handle(Request $request, Closure $next): Response
    {
        $region = strtoupper((string) $request->query('region', ''));

        if (array_key_exists($region, (array) config('catmarket.countries', []))) {
            Cookie::queue('petavilo_region', $region, 60 * 24 * 365);
        }

        return $next($request);
    }
}
