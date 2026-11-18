<?php

declare(strict_types=1);

namespace Modules\Breeder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Breeder\Models\BreederOffspring;
use Modules\PetTools\Models\Pet;
use Modules\PetTools\Models\PetRecord;

class HandoverController extends Controller
{
    public function show(string $token): View
    {
        $offspring = $this->find($token);
        $offspring->load(['records', 'litter.dam', 'litter.sire']);

        return view('breeder::pack', ['offspring' => $offspring, 'litter' => $offspring->litter, 'forBreeder' => false]);
    }

    public function claim(Request $request, string $token): RedirectResponse
    {
        $offspring = $this->find($token);

        if ($offspring->getAttribute('handover_claimed_at') !== null) {
            return redirect()->route('pettools.passport.index')->with('success', $offspring->getAttribute('name').' has already been added to a Pet Passport.');
        }

        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);
        $offspring->load(['records', 'litter']);
        $litter = $offspring->litter;

        $pet = DB::transaction(static function () use ($request, $offspring, $litter, $data): Pet {
            $pet = new Pet();
            $pet->fill([
                'name' => $data['name'],
                'species' => array_key_exists((string) $litter->getAttribute('species'), Pet::speciesOptions()) ? $litter->getAttribute('species') : 'other',
                'breed' => $litter->getAttribute('breed'),
                'sex' => match ($offspring->getAttribute('sex')) { 'male' => 'Male', 'female' => 'Female', default => null },
                'date_of_birth' => $litter->getAttribute('born_on')?->toDateString(),
                'colour' => $offspring->getAttribute('colour'),
                'microchip_number' => $offspring->getAttribute('microchip_number'),
                'medical_notes' => $offspring->getAttribute('care_notes'),
            ]);
            $pet->forceFill(['user_id' => $request->user()->getKey()])->save();

            foreach ($offspring->records as $record) {
                $type = match ($record->getAttribute('kind')) {
                    'weight' => PetRecord::TYPE_WEIGHT,
                    'vaccination' => PetRecord::TYPE_VACCINATION,
                    'parasite' => PetRecord::TYPE_PARASITE,
                    'vet_check' => PetRecord::TYPE_VET_VISIT,
                    default => PetRecord::TYPE_OTHER,
                };
                $grams = $record->getAttribute('grams');

                $copy = new PetRecord([
                    'type' => $type,
                    'title' => $record->label().' (from breeder)',
                    'happened_on' => $record->getAttribute('recorded_on')->toDateString(),
                    'value' => $grams !== null ? round($grams / 1000, 2) : null,
                    'notes' => $record->getAttribute('details'),
                ]);
                $copy->forceFill(['pet_id' => $pet->getKey()])->save();
            }

            $offspring->forceFill(['handover_claimed_at' => now()])->save();

            return $pet;
        });

        return redirect()->route('pettools.passport.show', $pet->getKey())->with('success', $pet->getAttribute('name').' is in your Pet Passport with the breeder\'s records. Add your vet and insurance when you have them.');
    }

    private function find(string $token): BreederOffspring
    {
        return BreederOffspring::query()->where('handover_token', $token)->firstOrFail();
    }
}
