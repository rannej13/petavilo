<?php

declare(strict_types=1);

namespace Modules\PetTools\Http\Controllers;

use App\Support\SpamGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Modules\CatMarket\Support\CatMarketSettings;
use Modules\PetTools\Models\TriageClinic;
use Modules\PetTools\Models\TriageSubmission;
use Modules\PetTools\Support\ClinicTriage;
use Modules\PetTools\Support\PainCheck;
use Throwable;

class ClinicTriageController extends Controller
{
    public function forVets(): View
    {
        return view('pettools::clinic.for-vets', [
            'country' => class_exists(CatMarketSettings::class) ? CatMarketSettings::visitorCountry() : 'GB',
        ]);
    }

    public function dashboard(Request $request): View
    {
        $clinic = TriageClinic::forUser((int) $request->user()->getKey());

        return view('pettools::clinic.dashboard', [
            'clinic' => $clinic,
            'submissions' => $clinic?->submissions()->latest()->limit(50)->get() ?? collect(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'booking_url' => ['nullable', 'url', 'max:200'],
            'emergency_text' => ['nullable', 'string', 'max:300'],
            'country' => ['required', Rule::in(['GB', 'US'])],
        ]);

        $clinic = TriageClinic::forUser((int) $request->user()->getKey());

        if ($clinic === null) {
            TriageClinic::start($request->user(), $data);
            \Modules\CatMarket\Support\SiteEmails::admin('New clinic trial: '.$data['name'], [
                $data['name'].' ('.$data['country'].') has started a 14-day trial of the triage widget.',
                'Contact: '.$data['email'],
            ], url('/admin'));

            return redirect()->route('pettools.clinic.dashboard')->with('success', 'Your 14-day free trial has started. Copy the embed code below onto your website.');
        }

        $clinic->fill($data)->save();

        return back()->with('success', 'Saved.');
    }

    public function widget(string $token): Response
    {
        $clinic = TriageClinic::findByToken($token);

        abort_if($clinic === null, 404);

        return $this->widgetResponse($clinic, null, []);
    }

    public function submit(Request $request, string $token): Response
    {
        $clinic = TriageClinic::findByToken($token);

        abort_if($clinic === null || ! $clinic->isLive(), 404);
        SpamGuard::check($request, 'owner_name');

        $questions = ClinicTriage::questions();
        $rules = [
            'owner_name' => ['required', 'string', 'max:80'],
            'owner_email' => ['nullable', 'email', 'max:160', 'required_without:owner_phone'],
            'owner_phone' => ['nullable', 'string', 'max:40', 'required_without:owner_email'],
            'pet_name' => ['nullable', 'string', 'max:60'],
            'age' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
        ];

        foreach ($questions as $key => $question) {
            $rules[$key] = ['required', Rule::in(array_map('strval', array_keys($question['options'])))];
        }

        foreach (array_keys(PainCheck::markers()) as $marker) {
            $rules['fgs_'.$marker] = ['nullable', 'integer', 'min:0', 'max:2'];
        }

        $data = $request->validate($rules);
        $scores = [];

        foreach (array_keys(PainCheck::markers()) as $marker) {
            if (isset($data['fgs_'.$marker]) && $data['fgs_'.$marker] !== '') {
                $scores[$marker] = (int) $data['fgs_'.$marker];
            }
        }

        $pain = ['score' => array_sum($scores), 'scored' => count($scores)];
        $assessment = ClinicTriage::assess($data, $pain['score']);
        $soap = ClinicTriage::soap($data, $pain, $assessment, (string) ($data['pet_name'] ?? ''), (string) ($data['age'] ?? ''));

        $submission = new TriageSubmission();
        $submission->fill([
            'tier' => $assessment['tier'],
            'owner_name' => $data['owner_name'],
            'owner_email' => $data['owner_email'] ?? null,
            'owner_phone' => $data['owner_phone'] ?? null,
            'pet_name' => $data['pet_name'] ?? null,
            'answers' => collect($data)->except(['consent', 'owner_name', 'owner_email', 'owner_phone'])->all(),
            'soap' => $soap,
        ]);
        $submission->forceFill(['clinic_id' => $clinic->getKey()])->save();

        try {
            $owner = $data['owner_name'].' · '.($data['owner_phone'] ?? '').' '.($data['owner_email'] ?? '');
            Mail::raw("Pre-exam triage #{$submission->getKey()} for {$clinic->name}\nOwner: {$owner}\n\n{$soap}", static function ($message) use ($clinic, $assessment, $submission): void {
                $message->to((string) $clinic->getAttribute('email'))
                    ->subject(strtoupper((string) $assessment['tier']).' triage #'.$submission->getKey().' '.($submission->pet_name ?: ''));
            });
            $submission->forceFill(['emailed_at' => now()])->save();
        } catch (Throwable $exception) {
            report($exception);
        }

        return $this->widgetResponse($clinic, $assessment, $data);
    }

    private function widgetResponse(TriageClinic $clinic, ?array $assessment, array $data): Response
    {
        return response()->view('pettools::clinic.widget', [
            'clinic' => $clinic,
            'questions' => ClinicTriage::questions(),
            'markers' => PainCheck::markers(),
            'assessment' => $assessment,
            'data' => $data,
        ])->header('X-Robots-Tag', 'noindex');
    }
}
