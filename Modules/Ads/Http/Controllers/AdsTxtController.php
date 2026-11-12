<?php

declare(strict_types=1);

namespace Modules\Ads\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Ads\Support\AdPlacement;

class AdsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $body = AdPlacement::adsTxt();

        abort_if($body === null, 404);

        return response($body."\n", 200)->header('Content-Type', 'text/plain');
    }
}
