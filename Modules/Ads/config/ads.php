<?php

declare(strict_types=1);

return [
    'client' => env('ADSENSE_CLIENT'),
    'infeed_every' => (int) env('ADSENSE_INFEED_EVERY', 6),
    'slots' => [
        'home' => env('ADSENSE_SLOT_HOME'),
        'listing_infeed' => env('ADSENSE_SLOT_LISTING_INFEED'),
        'listing_detail' => env('ADSENSE_SLOT_LISTING_DETAIL'),
        'guide_top' => env('ADSENSE_SLOT_GUIDE_TOP'),
        'guide_bottom' => env('ADSENSE_SLOT_GUIDE_BOTTOM'),
        'directory' => env('ADSENSE_SLOT_DIRECTORY'),
        'community' => env('ADSENSE_SLOT_COMMUNITY'),
    ],
    'infeed_slots' => ['listing_infeed'],
    'excluded_routes' => [
        'panel.*',
        'billing.*',
        'login',
        'register',
        'password.*',
        'verification.*',
        'favorites.*',
        'directory.claim*',
        'pages.show',
        'community.create',
    ],
];
