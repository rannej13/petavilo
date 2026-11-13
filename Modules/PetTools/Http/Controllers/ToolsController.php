<?php

declare(strict_types=1);

namespace Modules\PetTools\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\PetTools\Support\FoodMatcher;
use Modules\PetTools\Support\ToxinLibrary;

class ToolsController extends Controller
{
    public function index(): View
    {
        return view('pettools::index');
    }

    public function poison(Request $request): View
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 60);
        $species = array_key_exists((string) $request->query('pet'), ToxinLibrary::species()) ? (string) $request->query('pet') : null;
        $category = array_key_exists((string) $request->query('type'), ToxinLibrary::categories()) ? (string) $request->query('type') : null;

        return view('pettools::poison', [
            'query' => $query,
            'species' => $species,
            'category' => $category,
            'items' => ToxinLibrary::search($query, $species, $category),
            'speciesOptions' => ToxinLibrary::species(),
            'categories' => ToxinLibrary::categories(),
            'levels' => ToxinLibrary::levels(),
            'country' => $this->country(),
        ]);
    }

    public function food(Request $request): View
    {
        $questions = FoodMatcher::questions();
        $answers = [];

        foreach ($questions as $key => $question) {
            $value = (string) $request->query($key, '');

            if (array_key_exists($value, $question['options'])) {
                $answers[$key] = $value;
            }
        }

        return view('pettools::food', [
            'questions' => $questions,
            'answers' => $answers,
            'result' => isset($answers['species']) ? FoodMatcher::match($answers, $this->country()) : null,
        ]);
    }

    public function passportAbout(): View
    {
        return view('pettools::passport-about');
    }

    private function country(): string
    {
        return class_exists(CatMarketSettings::class) ? CatMarketSettings::visitorCountry() : 'GB';
    }
}
