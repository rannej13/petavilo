<?php

declare(strict_types=1);

namespace Modules\Directory\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Directory\Models\Business;

class BusinessPanelController extends Controller
{
    public function index(Request $request): View
    {
        return view('directory::panel', [
            'businesses' => Business::ownedBy((int) $request->user()->getKey()),
        ]);
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        abort_unless((int) $business->getAttribute('owner_user_id') === (int) $request->user()->getKey(), 403);

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:3000'],
            'address_line' => ['nullable', 'string', 'max:160'],
            'postcode' => ['nullable', 'string', 'max:12'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'string', 'max:200'],
            'booking_url' => ['nullable', 'string', 'max:200'],
            'social_url' => ['nullable', 'string', 'max:200'],
            'opening_hours' => ['nullable', 'string', 'max:600'],
            'licence_number' => ['nullable', 'string', 'max:80'],
            'services_text' => ['nullable', 'string', 'max:1500'],
            'price_from' => ['nullable', 'string', 'max:60'],
            'accreditation' => ['nullable', Rule::in(array_keys(Business::ACCREDITATIONS))],
            'new_client_offer' => ['nullable', 'string', 'max:160'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:5120'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['string'],
        ]);

        $data['services'] = collect(preg_split('/\R|,/', (string) ($data['services_text'] ?? '')) ?: [])
            ->map(static fn (string $line): string => Str::limit(trim($line), 60, ''))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
        $data['emergency_care'] = $request->boolean('emergency_care');
        $data['home_visits'] = $request->boolean('home_visits');
        $data['cat_only'] = $request->boolean('cat_only');
        $data['accreditation'] = $data['accreditation'] ?? null;

        $business->updateFromOwner($data);
        $this->storeMedia($request, $business);

        return back()->with('success', __('directory::messages.saved'));
    }

    private function storeMedia(Request $request, Business $business): void
    {
        $disk = 'public';
        $folder = 'businesses/'.$business->getKey();

        if ($request->hasFile('logo')) {
            $business->forceFill(['logo_path' => $request->file('logo')->store($folder, $disk)])->saveQuietly();
        }

        $photos = collect((array) ($business->getAttribute('photos') ?? []))
            ->reject(static fn (string $path): bool => in_array($path, (array) $request->input('remove_photos', []), true))
            ->values();

        foreach ((array) $request->file('photos', []) as $file) {
            if ($photos->count() >= $business->photoLimit()) {
                break;
            }

            $photos->push($file->store($folder, $disk));
        }

        $business->forceFill(['photos' => $photos->all()])->saveQuietly();
    }
}
