<?php

declare(strict_types=1);

return [
    'donations_enabled' => (bool) env('DONATIONS_ENABLED', true),
    'donation_presets' => [5, 10, 25, 50],
    'donation_min' => 2,
    'donation_max' => 5000,
    'card_fee' => [
        'GBP' => ['percent' => 1.5, 'fixed' => 0.20],
        'USD' => ['percent' => 2.9, 'fixed' => 0.30],
    ],
];
