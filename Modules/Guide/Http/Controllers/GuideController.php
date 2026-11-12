<?php

declare(strict_types=1);

namespace Modules\Guide\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Guide\Models\Guide;
use Modules\Guide\Support\GuideRenderer;

class GuideController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');
        $type = array_key_exists($type, Guide::TYPES) ? $type : null;

        return view('guide::index', [
            'guides' => Guide::browse($type),
            'types' => Guide::TYPES,
            'type' => $type,
        ]);
    }

    public function show(string $slug): View
    {
        $guide = Guide::findPublished($slug);

        abort_if($guide === null, 404);

        [$intro, $rest] = GuideRenderer::split(GuideRenderer::html((string) $guide->getAttribute('body')));

        return view('guide::show', [
            'guide' => $guide,
            'intro' => $intro,
            'rest' => $rest,
            'related' => $guide->related(),
        ]);
    }
}
