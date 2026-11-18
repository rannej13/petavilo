<?php

declare(strict_types=1);

namespace Modules\Breeder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Breeder\Models\BreederAnimal;
use Modules\Breeder\Models\BreederLitter;
use Modules\Breeder\Models\BreederOffspring;
use Modules\Breeder\Models\BreederRecord;
use Modules\Breeder\Models\BreederWaitlist;
use Modules\Breeder\Support\BreedingChecks;
use Modules\PetTools\Models\Pet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BreederController extends Controller
{
    public function dashboard(Request $request): View
    {
        $userId = (int) $request->user()->getKey();
        $litters = BreederLitter::query()->where('user_id', $userId)->with(['dam', 'sire', 'offspring'])->orderByRaw("CASE WHEN status = 'closed' THEN 1 ELSE 0 END")->orderByDesc('id')->get();

        return view('breeder::dashboard', [
            'litters' => $litters,
            'animals' => BreederAnimal::query()->where('user_id', $userId)->where('is_active', true)->orderBy('name')->get(),
            'waitlist' => BreederWaitlist::query()->where('user_id', $userId)->whereIn('status', ['waiting', 'offered'])->with('litter:id,name')->orderBy('id')->get(),
            'warnings' => $litters->where('status', '!=', 'closed')->mapWithKeys(static fn (BreederLitter $l): array => [$l->getKey() => BreedingChecks::forLitter($l)])->filter(),
        ]);
    }

    // Dams and sires

    public function createAnimal(): View
    {
        return view('breeder::animals.form', ['animal' => new BreederAnimal(['sex' => 'female', 'species' => 'dog', 'is_active' => true])]);
    }

    public function storeAnimal(Request $request): RedirectResponse
    {
        $animal = new BreederAnimal($this->animalData($request));
        $animal->forceFill(['user_id' => $request->user()->getKey()])->save();

        return redirect()->route('breeder.dashboard')->with('success', $animal->getAttribute('name').' added.');
    }

    public function editAnimal(Request $request, int $animal): View
    {
        return view('breeder::animals.form', ['animal' => $this->ownAnimal($request, $animal)]);
    }

    public function updateAnimal(Request $request, int $animal): RedirectResponse
    {
        $record = $this->ownAnimal($request, $animal);
        $record->fill($this->animalData($request))->save();

        return redirect()->route('breeder.dashboard')->with('success', 'Saved.');
    }

    // Litters

    public function createLitter(Request $request): View
    {
        return view('breeder::litters.form', [
            'litter' => new BreederLitter(['species' => 'dog', 'status' => 'planned']),
            'dams' => $this->animals($request, 'female'),
            'sires' => $this->animals($request, 'male'),
        ]);
    }

    public function storeLitter(Request $request): RedirectResponse
    {
        $litter = new BreederLitter($this->litterData($request));
        $litter->forceFill(['user_id' => $request->user()->getKey()])->save();

        return redirect()->route('breeder.litters.show', $litter->getKey())->with('success', 'Litter saved.');
    }

    public function editLitter(Request $request, int $litter): View
    {
        return view('breeder::litters.form', [
            'litter' => $this->ownLitter($request, $litter),
            'dams' => $this->animals($request, 'female'),
            'sires' => $this->animals($request, 'male'),
        ]);
    }

    public function updateLitter(Request $request, int $litter): RedirectResponse
    {
        $record = $this->ownLitter($request, $litter);
        $record->fill($this->litterData($request))->save();

        return redirect()->route('breeder.litters.show', $record->getKey())->with('success', 'Saved.');
    }

    public function showLitter(Request $request, int $litter): View
    {
        $record = $this->ownLitter($request, $litter);
        $record->load(['dam', 'sire', 'offspring.records', 'waitlist']);

        return view('breeder::litters.show', [
            'litter' => $record,
            'warnings' => BreedingChecks::forLitter($record),
            'shared' => BreederRecord::query()->whereIn('offspring_id', $record->offspring->modelKeys())->where('kind', '!=', 'weight')->orderByDesc('recorded_on')->get()->groupBy(static fn ($r) => $r->getAttribute('kind').'|'.$r->getAttribute('recorded_on')->toDateString().'|'.$r->getAttribute('title')),
            'advert' => $this->advertText($record),
        ]);
    }

    public function addOffspring(Request $request, int $litter): RedirectResponse
    {
        $record = $this->ownLitter($request, $litter);
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:20'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
        ]);
        $start = $record->offspring()->count();

        for ($i = 1; $i <= (int) $data['count']; $i++) {
            $record->offspring()->create(['name' => ($start + $i).'. '.($data['sex'] ? ucfirst($data['sex']) : 'Unnamed'), 'sex' => $data['sex'] ?? null, 'status' => 'available']);
        }

        if ($record->getAttribute('status') === 'expected' || $record->getAttribute('status') === 'planned') {
            $record->forceFill(['status' => 'born', 'born_on' => $record->getAttribute('born_on') ?? now()->toDateString()])->save();
        }

        return back()->with('success', 'Added. Give each one a collar colour or name so you can tell them apart.');
    }

    public function updateOffspring(Request $request, int $offspring): RedirectResponse
    {
        $record = $this->ownOffspring($request, $offspring);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'sex' => ['nullable', Rule::in(['male', 'female'])],
            'colour' => ['nullable', 'string', 'max:120'],
            'microchip_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(array_keys(BreederOffspring::STATUSES))],
            'buyer_name' => ['nullable', 'string', 'max:120'],
            'buyer_email' => ['nullable', 'email', 'max:160'],
            'buyer_town' => ['nullable', 'string', 'max:80'],
            'care_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previous = $record->getAttribute('status');
        $record->fill($data);

        if ($data['status'] === 'reserved' && $previous !== 'reserved') {
            $record->forceFill(['reserved_at' => now()]);
        }

        if ($data['status'] === 'sold' && $previous !== 'sold') {
            $record->forceFill(['sold_at' => now()]);
        }

        $record->save();
        $this->closeIfAllHomed($record->litter);

        return back()->with('success', $record->getAttribute('name').' saved.');
    }

    public function storeWeights(Request $request, int $litter): RedirectResponse
    {
        $record = $this->ownLitter($request, $litter);
        $data = $request->validate([
            'recorded_on' => ['required', 'date', 'before_or_equal:today'],
            'grams' => ['required', 'array'],
            'grams.*' => ['nullable', 'integer', 'min:1', 'max:200000'],
        ]);
        $ids = $record->offspring()->pluck('id')->all();
        $saved = 0;

        foreach ($data['grams'] as $id => $grams) {
            if ($grams === null || ! in_array((int) $id, $ids, true)) {
                continue;
            }

            $weight = BreederRecord::query()->firstOrNew(['offspring_id' => (int) $id, 'kind' => 'weight', 'recorded_on' => $data['recorded_on']]);
            $weight->forceFill(['offspring_id' => (int) $id, 'kind' => 'weight', 'recorded_on' => $data['recorded_on'], 'grams' => (int) $grams])->save();
            $saved++;
        }

        return back()->with('success', $saved.' '.Str::plural('weight', $saved).' saved for '.Carbon::parse($data['recorded_on'])->isoFormat('D MMM').'.');
    }

    public function storeRecord(Request $request, int $litter): RedirectResponse
    {
        $record = $this->ownLitter($request, $litter);
        $data = $request->validate([
            'kind' => ['required', Rule::in(array_diff(array_keys(BreederRecord::KINDS), ['weight']))],
            'recorded_on' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:1000'],
            'offspring' => ['required', 'array', 'min:1'],
            'offspring.*' => ['integer'],
        ]);
        $ids = array_values(array_intersect($record->offspring()->pluck('id')->all(), array_map('intval', $data['offspring'])));

        DB::transaction(static function () use ($ids, $data): void {
            foreach ($ids as $id) {
                (new BreederRecord([
                    'kind' => $data['kind'],
                    'recorded_on' => $data['recorded_on'],
                    'title' => $data['title'] ?? null,
                    'details' => $data['details'] ?? null,
                ]))->forceFill(['offspring_id' => $id])->save();
            }
        });

        return back()->with('success', BreederRecord::KINDS[$data['kind']].' recorded for '.count($ids).'.');
    }

    // Waiting list

    public function storeWaitlist(Request $request): RedirectResponse
    {
        $entry = new BreederWaitlist($this->waitlistData($request));
        $entry->forceFill(['user_id' => $request->user()->getKey()])->save();

        return back()->with('success', $entry->getAttribute('name').' added to your waiting list.');
    }

    public function updateWaitlist(Request $request, int $entry): RedirectResponse
    {
        $record = BreederWaitlist::query()->where('user_id', $request->user()->getKey())->findOrFail($entry);
        $record->fill($this->waitlistData($request))->save();

        return back()->with('success', 'Saved.');
    }

    // Licence register

    public function register(Request $request): View
    {
        return view('breeder::register', ['litters' => $this->registerRows($request), 'source' => BreedingChecks::SOURCE_URL]);
    }

    public function registerCsv(Request $request): StreamedResponse
    {
        $litters = $this->registerRows($request);

        return response()->streamDownload(static function () use ($litters): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Litter', 'Species', 'Breed', 'Dam', 'Dam date of birth', 'Dam microchip', 'Sire', 'Mated', 'Born', 'Caesarean', 'Born count', 'Offspring', 'Sex', 'Microchip', 'Status', 'New owner', 'Town', 'Went home']);

            foreach ($litters as $litter) {
                foreach ($litter->offspring->isEmpty() ? [null] : $litter->offspring as $o) {
                    fputcsv($out, [
                        $litter->getAttribute('name'), $litter->getAttribute('species'), $litter->getAttribute('breed'),
                        $litter->dam?->getAttribute('name'), $litter->dam?->getAttribute('date_of_birth')?->toDateString(), $litter->dam?->getAttribute('microchip_number'),
                        $litter->sireLabel(), $litter->getAttribute('mated_on')?->toDateString(), $litter->getAttribute('born_on')?->toDateString(),
                        $litter->getAttribute('caesarean') ? 'Yes' : 'No', $litter->getAttribute('born_count'),
                        $o?->getAttribute('name'), $o?->getAttribute('sex'), $o?->getAttribute('microchip_number'),
                        $o ? (BreederOffspring::STATUSES[$o->getAttribute('status')] ?? $o->getAttribute('status')) : null,
                        $o?->getAttribute('buyer_name'), $o?->getAttribute('buyer_town'), $o?->getAttribute('sold_at')?->toDateString(),
                    ]);
                }
            }

            fclose($out);
        }, 'breeding-register-'.now()->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }

    // Handover

    public function startHandover(Request $request, int $offspring): RedirectResponse
    {
        $record = $this->ownOffspring($request, $offspring);
        $data = $request->validate([
            'buyer_name' => ['required', 'string', 'max:120'],
            'buyer_email' => ['nullable', 'email', 'max:160'],
            'buyer_town' => ['nullable', 'string', 'max:80'],
        ]);

        $record->fill($data);
        $record->forceFill([
            'status' => 'sold',
            'sold_at' => $record->getAttribute('sold_at') ?? now(),
            'handover_token' => $record->getAttribute('handover_token') ?: Str::random(40),
        ])->save();
        $this->closeIfAllHomed($record->litter);

        return redirect()->route('breeder.pack', $record->getKey())->with('success', 'Handover pack ready. Give the new owner the link or QR code so they can add '.$record->getAttribute('name').' to their free Pet Passport.');
    }

    public function pack(Request $request, int $offspring): View|RedirectResponse
    {
        $record = $this->ownOffspring($request, $offspring);

        if (blank($record->getAttribute('handover_token'))) {
            return redirect()->route('breeder.litters.show', $record->getAttribute('litter_id'))->with('error', 'Create the handover pack first.');
        }
        $record->load(['records', 'litter.dam', 'litter.sire']);

        return view('breeder::pack', ['offspring' => $record, 'litter' => $record->litter, 'forBreeder' => true]);
    }

    // Helpers

    private function closeIfAllHomed(?BreederLitter $litter): void
    {
        if ($litter === null) {
            return;
        }

        $open = $litter->offspring()->whereIn('status', ['available', 'reserved'])->count();

        if ($open === 0 && $litter->offspring()->count() > 0 && $litter->getAttribute('status') !== 'closed') {
            $litter->forceFill(['status' => 'closed'])->save();
        }
    }

    private function advertText(BreederLitter $litter): array
    {
        $species = Pet::speciesOptions()[$litter->getAttribute('species')] ?? 'Pet';
        $young = ['dog' => 'puppies', 'cat' => 'kittens', 'rabbit' => 'kits', 'guinea-pig' => 'pups'][$litter->getAttribute('species')] ?? 'babies';
        $counts = $litter->counts();
        $title = trim(($litter->getAttribute('breed') ?: $species).' '.$young.($litter->getAttribute('ready_on') ? ', ready '.$litter->getAttribute('ready_on')->isoFormat('D MMMM') : ''));
        $lines = [];

        if ($litter->getAttribute('born_on')) {
            $lines[] = 'Born '.$litter->getAttribute('born_on')->isoFormat('D MMMM YYYY').'.';
        }

        if ($counts['available'] > 0) {
            $males = $litter->offspring->where('status', 'available')->where('sex', 'male')->count();
            $females = $litter->offspring->where('status', 'available')->where('sex', 'female')->count();
            $lines[] = $counts['available'].' available'.($males + $females > 0 ? ' ('.$males.' '.Str::plural('boy', $males).', '.$females.' '.Str::plural('girl', $females).')' : '').'.';
        }

        $lines[] = 'Mum: '.($litter->dam?->getAttribute('name') ?: 'see in person').'. Dad: '.$litter->sireLabel().'.';

        if ($litter->dam?->getAttribute('health_tests')) {
            $lines[] = 'Parent health tests: '.$litter->dam->getAttribute('health_tests').'.';
        }

        $vaccinated = $litter->offspring->every(static fn ($o) => $o->records->contains('kind', 'vaccination'));
        $wormed = $litter->offspring->every(static fn ($o) => $o->records->contains('kind', 'parasite'));
        $chipped = $litter->offspring->every(static fn ($o) => filled($o->getAttribute('microchip_number')) || $o->records->contains('kind', 'microchip'));
        $done = array_keys(array_filter(['first vaccinations' => $vaccinated, 'wormed and flea treated' => $wormed, 'microchipped' => $chipped]));

        if ($litter->offspring->isNotEmpty() && $done !== []) {
            $lines[] = 'All '.$young.' have been '.implode(', ', $done).'.';
        }

        $lines[] = 'Each one goes home with its weight chart, health records and a free Petavilo Pet Passport. You are welcome to see them with their mum before paying anything.';

        return ['title' => Str::limit($title, 80, ''), 'body' => implode("\n", $lines)];
    }

    private function registerRows(Request $request)
    {
        return BreederLitter::query()->where('user_id', $request->user()->getKey())
            ->whereNotNull('born_on')->with(['dam', 'sire', 'offspring'])->orderByDesc('born_on')->get();
    }

    private function animals(Request $request, string $sex)
    {
        return BreederAnimal::query()->where('user_id', $request->user()->getKey())->where('sex', $sex)->where('is_active', true)->orderBy('name')->get();
    }

    private function ownAnimal(Request $request, int $id): BreederAnimal
    {
        return BreederAnimal::query()->where('user_id', $request->user()->getKey())->findOrFail($id);
    }

    private function ownLitter(Request $request, int $id): BreederLitter
    {
        return BreederLitter::query()->where('user_id', $request->user()->getKey())->findOrFail($id);
    }

    private function ownOffspring(Request $request, int $id): BreederOffspring
    {
        return BreederOffspring::query()->whereHas('litter', static fn ($q) => $q->where('user_id', $request->user()->getKey()))->findOrFail($id);
    }

    private function animalData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'species' => ['required', Rule::in(array_keys(Pet::speciesOptions()))],
            'breed' => ['nullable', 'string', 'max:80'],
            'sex' => ['required', Rule::in(['female', 'male'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'microchip_number' => ['nullable', 'string', 'max:30'],
            'registration' => ['nullable', 'string', 'max:80'],
            'health_tests' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['is_active'] = ! $request->boolean('retired');

        return $data;
    }

    private function litterData(Request $request): array
    {
        $userId = (int) $request->user()->getKey();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'species' => ['required', Rule::in(array_keys(Pet::speciesOptions()))],
            'breed' => ['nullable', 'string', 'max:80'],
            'dam_id' => ['nullable', Rule::exists('breeder_animals', 'id')->where('user_id', $userId)],
            'sire_id' => ['nullable', Rule::exists('breeder_animals', 'id')->where('user_id', $userId)],
            'sire_name' => ['nullable', 'string', 'max:120'],
            'mated_on' => ['nullable', 'date'],
            'due_on' => ['nullable', 'date'],
            'born_on' => ['nullable', 'date', 'before_or_equal:today'],
            'born_count' => ['nullable', 'integer', 'min:0', 'max:30'],
            'ready_on' => ['nullable', 'date'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(array_keys(BreederLitter::STATUSES))],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $data['caesarean'] = $request->boolean('caesarean');

        if (blank($data['due_on'] ?? null) && filled($data['mated_on'] ?? null)) {
            $data['due_on'] = BreedingChecks::suggestDue($data['species'], Carbon::parse($data['mated_on']));
        }

        if (blank($data['ready_on'] ?? null) && filled($data['born_on'] ?? null)) {
            $data['ready_on'] = BreedingChecks::suggestReady($data['species'], Carbon::parse($data['born_on']));
        }

        return $data;
    }

    private function waitlistData(Request $request): array
    {
        $userId = (int) $request->user()->getKey();

        $data = $request->validate([
            'litter_id' => ['nullable', Rule::exists('breeder_litters', 'id')->where('user_id', $userId)],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'town' => ['nullable', 'string', 'max:80'],
            'preference' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(array_keys(BreederWaitlist::STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['status'] = ($data['status'] ?? null) ?: 'waiting';

        return $data;
    }
}
