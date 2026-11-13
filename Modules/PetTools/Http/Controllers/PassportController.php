<?php

declare(strict_types=1);

namespace Modules\PetTools\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\PetTools\Models\Pet;
use Modules\PetTools\Models\PetRecord;

class PassportController extends Controller
{
    public function index(Request $request): View
    {
        return view('pettools::passport.index', ['pets' => Pet::forOwner((int) $request->user()->getKey())]);
    }

    public function create(): View
    {
        return view('pettools::passport.form', ['pet' => new Pet()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pet = new Pet();
        $pet->fill($this->validated($request));
        $pet->forceFill(['user_id' => $request->user()->getKey()])->save();
        $this->savePhoto($request, $pet);

        return redirect()->route('pettools.passport.show', $pet->getKey())->with('success', $pet->getAttribute('name').' has been added.');
    }

    public function show(Request $request, int $pet): View
    {
        $record = $this->owned($request, $pet);
        $record->load(['records' => static fn ($query) => $query->orderByDesc('happened_on')->orderByDesc('id')]);

        return view('pettools::passport.show', ['pet' => $record, 'types' => PetRecord::types(), 'passFields' => Pet::PASS_FIELDS]);
    }

    public function edit(Request $request, int $pet): View
    {
        return view('pettools::passport.form', ['pet' => $this->owned($request, $pet)]);
    }

    public function update(Request $request, int $pet): RedirectResponse
    {
        $record = $this->owned($request, $pet);
        $record->fill($this->validated($request))->save();
        $this->savePhoto($request, $record);

        return redirect()->route('pettools.passport.show', $record->getKey())->with('success', 'Saved.');
    }

    public function destroy(Request $request, int $pet): RedirectResponse
    {
        $this->owned($request, $pet)->delete();

        return redirect()->route('pettools.passport.index')->with('success', 'Pet removed.');
    }

    public function storeRecord(Request $request, int $pet): RedirectResponse
    {
        $record = $this->owned($request, $pet);
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(PetRecord::types()))],
            'title' => ['required', 'string', 'max:120'],
            'happened_on' => ['nullable', 'date', 'before_or_equal:today'],
            'due_on' => ['nullable', 'date'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $record->records()->create($data);

        return back()->with('success', 'Record added.');
    }

    public function destroyRecord(Request $request, int $pet, int $record): RedirectResponse
    {
        $this->owned($request, $pet)->records()->whereKey($record)->firstOrFail()->delete();

        return back()->with('success', 'Record removed.');
    }

    public function updatePass(Request $request, int $pet): RedirectResponse
    {
        $record = $this->owned($request, $pet);
        $action = (string) $request->input('action');

        match ($action) {
            'disable' => $record->disablePass(),
            'reset' => $record->resetPass(),
            default => $record->enablePass((array) $request->input('fields', [])),
        };

        return back()->with('success', match ($action) {
            'disable' => 'Emergency pass switched off. The QR code no longer works.',
            'reset' => 'New QR code made. Old printed codes no longer work.',
            default => 'Emergency pass is on.',
        });
    }

    public function toggleMissing(Request $request, int $pet): RedirectResponse
    {
        $record = $this->owned($request, $pet);
        $record->forceFill(['is_missing' => ! $record->getAttribute('is_missing')])->save();

        return back()->with('success', $record->getAttribute('is_missing')
            ? 'Marked as missing. The emergency pass now asks finders to contact you straight away. Post a free Lost advert too.'
            : 'Welcome home! Missing banner removed.');
    }

    public function pass(string $token): Response
    {
        $pet = Pet::findByPass($token);

        abort_if($pet === null, 404);

        return response()->view('pettools::pass', ['pet' => $pet])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function owned(Request $request, int $pet): Pet
    {
        return Pet::query()->ownedBy((int) $request->user()->getKey())->findOrFail($pet);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'species' => ['required', Rule::in(array_keys(Pet::speciesOptions()))],
            'breed' => ['nullable', 'string', 'max:80'],
            'sex' => ['nullable', Rule::in(['Male', 'Female', 'Unknown'])],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'colour' => ['nullable', 'string', 'max:120'],
            'neutered' => ['nullable', Rule::in(['Yes', 'No', 'Unknown'])],
            'microchip_number' => ['nullable', 'string', 'max:30'],
            'microchip_database' => ['nullable', 'string', 'max:80'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'medications' => ['nullable', 'string', 'max:1000'],
            'diet' => ['nullable', 'string', 'max:200'],
            'vet_name' => ['nullable', 'string', 'max:120'],
            'vet_phone' => ['nullable', 'string', 'max:40'],
            'insurer' => ['nullable', 'string', 'max:120'],
            'policy_number' => ['nullable', 'string', 'max:60'],
            'policy_type' => ['nullable', Rule::in(['Lifetime', 'Maximum benefit', 'Time limited', 'Accident only', 'Other'])],
            'policy_renewal' => ['nullable', 'date'],
            'policy_excess' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'policy_limit' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'insurer_claims_phone' => ['nullable', 'string', 'max:40'],
            'owner_phone' => ['nullable', 'string', 'max:40'],
            'emergency_contact' => ['nullable', 'string', 'max:120'],
            'emergency_phone' => ['nullable', 'string', 'max:40'],
        ]);
    }

    private function savePhoto(Request $request, Pet $pet): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        $request->validate(['photo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192']]);
        $pet->storePhoto($request->file('photo'));
    }
}
