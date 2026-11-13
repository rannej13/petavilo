<?php

declare(strict_types=1);

namespace Modules\PetTools\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Modules\PetTools\Models\VetLead;
use Throwable;
use Illuminate\Routing\Controller;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\PetTools\Support\CalorieCalculator;
use Modules\PetTools\Support\DentalGrader;
use Modules\PetTools\Support\FoodMatcher;
use Modules\PetTools\Support\InsuranceSimulator;
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

    public function insurance(Request $request): View
    {
        $country = $this->country();
        $defaults = InsuranceSimulator::defaults($country);
        $input = [];

        foreach ($defaults as $key => $value) {
            $input[$key] = is_numeric($request->query($key)) ? (float) $request->query($key) : $value;
        }

        $age = (string) $request->query('age', '');
        $conditions = (string) $request->query('conditions', '');

        return view('pettools::insurance', [
            'input' => $input,
            'age' => $age,
            'conditions' => $conditions,
            'result' => InsuranceSimulator::run($input),
            'symbol' => $country === 'US' ? '$' : '£',
            'country' => $country,
            'insurers' => Partners::insurers($country),
        ]);
    }

    public function dental(Request $request): View
    {
        $gums = (int) $request->query('gums', 0);
        $age = (string) $request->query('age', '');

        return view('pettools::dental', [
            'gumStages' => DentalGrader::gumStages(),
            'signs' => DentalGrader::signs(),
            'seniorSigns' => DentalGrader::seniorSigns(),
            'input' => $request->query(),
            'result' => $gums > 0 ? DentalGrader::grade($gums, (array) $request->query('signs', []), $age, (array) $request->query('senior', [])) : null,
            'country' => $this->country(),
        ]);
    }

    public function storeLead(Request $request): RedirectResponse
    {
        \App\Support\SpamGuard::check($request, 'email');

        $data = $request->validate([
            'kind' => ['required', Rule::in([VetLead::KIND_DENTAL, VetLead::KIND_SENIOR])],
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'town' => ['required', 'string', 'max:80'],
            'pet_name' => ['nullable', 'string', 'max:60'],
            'summary' => ['nullable', 'string', 'max:300'],
            'consent' => ['accepted'],
        ]);

        $lead = new VetLead();
        $lead->fill([...$data, 'country' => $this->country(), 'answers' => $request->except(['_token', 'name', 'email', 'phone', 'consent', 'company_website', 'form_stamp'])]);
        $lead->forceFill(['consented_at' => now()])->save();

        try {
            $to = (string) config('catmarket.company.email');

            if ($to !== '') {
                Mail::raw("New {$lead->kind} check request #{$lead->getKey()}\n{$lead->name} ({$lead->email} {$lead->phone})\n{$lead->town}, {$lead->country}\nPet: {$lead->pet_name}\n{$lead->summary}", static function ($message) use ($to): void {
                    $message->to($to)->subject('New vet check request');
                });
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Thanks. We will pass your request to a vet near '.$lead->town.' and they will contact you to book. Reference #'.$lead->getKey().'.');
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
