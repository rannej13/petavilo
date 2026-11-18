<?php

declare(strict_types=1);

namespace Modules\Tv\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\PetTools\Models\Pet;
use Modules\Tv\Models\PetOfWeekEntry;
use Modules\Tv\Models\PetOfWeekVote;

class PetOfWeekController extends Controller
{
    public function index(Request $request): View
    {
        $week = PetOfWeekEntry::currentWeek();
        $entries = PetOfWeekEntry::query()
            ->with('user:id,name')
            ->whereDate('week_start', $week)
            ->where('status', PetOfWeekEntry::STATUS_APPROVED)
            ->orderByDesc('votes_count')
            ->orderBy('id')
            ->get();

        $userId = $request->user()?->getKey();
        $myVote = $userId === null ? null : PetOfWeekVote::query()->where('user_id', $userId)->whereDate('week_start', $week)->value('entry_id');
        $myEntry = $userId === null ? null : PetOfWeekEntry::query()->where('user_id', $userId)->whereDate('week_start', $week)->first();

        $winners = PetOfWeekEntry::query()
            ->with('user:id,name')
            ->where('status', PetOfWeekEntry::STATUS_WINNER)
            ->where('consent_feature', true)
            ->latest('week_start')
            ->limit(6)
            ->get();

        return view('tv::pet-of-week', [
            'entries' => $entries,
            'week' => $week,
            'closes' => $week->copy()->addDays(6)->endOfDay(),
            'myVote' => $myVote === null ? null : (int) $myVote,
            'myEntry' => $myEntry,
            'winners' => $winners,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $existing = PetOfWeekEntry::query()->where('user_id', $request->user()->getKey())->whereDate('week_start', PetOfWeekEntry::currentWeek())->first();

        if ($existing !== null) {
            return redirect()->route('tv.potw')->with('success', 'You have already entered this week. You can enter again next Monday.');
        }

        $pets = class_exists(Pet::class)
            ? Pet::query()->where('user_id', $request->user()->getKey())->orderBy('name')->get(['id', 'name', 'species'])
            : collect();

        return view('tv::pet-of-week-enter', ['pets' => $pets, 'species' => Pet::speciesOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pet_name' => ['required', 'string', 'max:60'],
            'species' => ['required', Rule::in(array_keys(Pet::speciesOptions()))],
            'caption' => ['nullable', 'string', 'max:200'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'own_photo' => ['accepted'],
            'consent_feature' => ['accepted'],
        ], [
            'own_photo.accepted' => 'Please confirm the photo is yours and the pet is yours.',
            'consent_feature.accepted' => 'Please agree that we can show the photo on Petavilo and its social pages.',
        ]);

        $entry = new PetOfWeekEntry();
        $entry->fill([
            'pet_name' => $data['pet_name'],
            'species' => $data['species'],
            'caption' => $data['caption'] ?? null,
            'consent_feature' => true,
        ]);
        $entry->forceFill([
            'user_id' => $request->user()->getKey(),
            'week_start' => PetOfWeekEntry::currentWeek()->toDateString(),
            'photo_path' => $request->file('photo')->store('pet-of-week', 'public'),
            'status' => PetOfWeekEntry::STATUS_PENDING,
        ]);

        try {
            $entry->save();
        } catch (QueryException) {
            return redirect()->route('tv.potw')->with('success', 'You have already entered this week.');
        }

        return redirect()->route('tv.potw')->with('success', 'Thanks! We check every photo, usually within a day. You will get a notification when '.$data['pet_name'].' is in the running.');
    }

    public function vote(Request $request, PetOfWeekEntry $entry): RedirectResponse
    {
        $week = PetOfWeekEntry::currentWeek();
        $userId = (int) $request->user()->getKey();

        if ($entry->getAttribute('status') !== PetOfWeekEntry::STATUS_APPROVED || ! $entry->getAttribute('week_start')->isSameDay($week)) {
            return back()->with('error', 'Voting for this pet has closed.');
        }

        if ((int) $entry->getAttribute('user_id') === $userId) {
            return back()->with('error', 'You cannot vote for your own pet, but you can share it with friends.');
        }

        try {
            DB::transaction(static function () use ($entry, $userId, $week): void {
                PetOfWeekVote::query()->create(['entry_id' => $entry->getKey(), 'user_id' => $userId, 'week_start' => $week->toDateString()]);
                $entry->increment('votes_count');
            });
        } catch (QueryException) {
            return back()->with('error', 'You have already voted this week. One vote per person keeps it fair.');
        }

        return back()->with('success', 'Vote counted for '.$entry->getAttribute('pet_name').'. Voting closes on Sunday night.');
    }
}
