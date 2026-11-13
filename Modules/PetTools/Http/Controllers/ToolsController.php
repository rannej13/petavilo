<?php

declare(strict_types=1);

namespace Modules\PetTools\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\PetTools\Support\CalorieCalculator;
use Modules\PetTools\Support\FoodMatcher;
use Modules\PetTools\Support\LitterForensics;
use Modules\PetTools\Support\PainCheck;
use Modules\PetTools\Support\Partners;
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

    public function calories(Request $request): View
    {
        $species = $request->query('species') === 'dog' ? 'dog' : 'cat';
        $stage = (string) $request->query('stage', $species === 'dog' ? 'adult' : 'indoor');
        $weight = (float) str_replace(',', '.', (string) $request->query('weight', ''));
        $density = (float) $request->query('kcal', 0);
        $oldDensity = (float) $request->query('old_kcal', 0);
        $rejected = (string) $request->query('rejected', '');
        $protein = mb_substr(trim((string) $request->query('protein', '')), 0, 30);
        $country = $this->country();
        $tag = (string) config($country === 'US' ? 'catmarket.amazon.us' : 'catmarket.amazon.uk', '');
        $host = $country === 'US' ? 'https://www.amazon.com' : 'https://www.amazon.co.uk';
        $textures = CalorieCalculator::textures();

        return view('pettools::calories', [
            'species' => $species,
            'stage' => $stage,
            'weight' => $weight > 0 ? $weight : null,
            'density' => $density > 0 ? $density : null,
            'oldDensity' => $oldDensity > 0 ? $oldDensity : null,
            'factors' => CalorieCalculator::factors(),
            'textures' => $textures,
            'rejected' => array_key_exists($rejected, $textures) ? $rejected : null,
            'protein' => $protein,
            'result' => $weight > 0 ? CalorieCalculator::calculate($species, $weight, $stage, $density, $oldDensity) : null,
            'matches' => array_key_exists($rejected, $textures) ? collect(CalorieCalculator::alternatives($rejected))->map(static fn (string $key): array => [
                'label' => $textures[$key],
                'url' => $host.'/s?k='.rawurlencode(trim('complete cat food '.$textures[$key].' '.$protein)).($tag !== '' ? '&tag='.rawurlencode($tag) : ''),
            ])->all() : [],
            'affiliate' => $tag !== '',
        ]);
    }

    public function litter(Request $request): View
    {
        $questions = LitterForensics::questions();
        $answers = [];

        foreach ($questions as $key => $question) {
            $value = (string) $request->query($key, '');

            if (array_key_exists($value, $question['options'])) {
                $answers[$key] = $value;
            }
        }

        $country = $this->country();
        $tag = (string) config($country === 'US' ? 'catmarket.amazon.us' : 'catmarket.amazon.uk', '');
        $host = $country === 'US' ? 'https://www.amazon.com' : 'https://www.amazon.co.uk';
        $result = isset($answers['medical']) ? LitterForensics::diagnose($answers) : null;

        return view('pettools::litter', [
            'questions' => $questions,
            'answers' => $answers,
            'result' => $result,
            'shopping' => collect($result['shopping'] ?? [])->map(static fn (string $search): array => [
                'label' => ucfirst($search),
                'url' => $host.'/s?k='.rawurlencode($search).($tag !== '' ? '&tag='.rawurlencode($tag) : ''),
            ])->all(),
            'affiliate' => $tag !== '',
        ]);
    }

    public function painCheck(Request $request): View
    {
        $submitted = $request->has('done');
        $scores = [];

        foreach (array_keys(PainCheck::markers()) as $key) {
            if ($request->filled($key)) {
                $scores[$key] = (int) $request->query($key);
            }
        }

        $country = $this->country();

        return view('pettools::pain-check', [
            'markers' => PainCheck::markers(),
            'redFlags' => PainCheck::redFlags(),
            'concerns' => PainCheck::concerns(),
            'input' => $request->query(),
            'result' => $submitted ? PainCheck::assess($scores, (array) $request->query('flags', []), (array) $request->query('concerns', [])) : null,
            'televet' => Partners::televet($country),
            'country' => $country,
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
