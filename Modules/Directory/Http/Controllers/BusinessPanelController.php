<?php

declare(strict_types=1);

namespace Modules\Directory\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'string', 'max:200'],
            'opening_hours' => ['nullable', 'string', 'max:600'],
            'licence_number' => ['nullable', 'string', 'max:80'],
        ]);

        $business->updateFromOwner($data);

        return back()->with('success', __('directory::messages.saved'));
    }
}
