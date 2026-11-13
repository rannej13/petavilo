<?php

declare(strict_types=1);

return [
    'breeder_credit_plan' => env('BREEDER_CREDIT_PLAN', 'featured-7'),
    'breeder_credits_per_month' => (int) env('BREEDER_CREDITS_PER_MONTH', 2),
    'memberships' => [
        'breeder' => [
            'type' => 'breeder',
            'price_id' => env('STRIPE_PRICE_BREEDER'),
            'price_id_usd' => env('STRIPE_PRICE_BREEDER_USD'),
            'amount' => '9.99',
            'amount_usd' => '11.99',
            'audience' => 'breeder',
        ],
        'directory-plus' => [
            'type' => 'directory',
            'price_id' => env('STRIPE_PRICE_DIRECTORY_PLUS'),
            'price_id_usd' => env('STRIPE_PRICE_DIRECTORY_PLUS_USD'),
            'amount' => '9.00',
            'amount_usd' => '11.00',
            'audience' => 'business',
            'tier' => 'plus',
        ],
        'directory-premium' => [
            'type' => 'directory',
            'price_id' => env('STRIPE_PRICE_DIRECTORY_PREMIUM'),
            'price_id_usd' => env('STRIPE_PRICE_DIRECTORY_PREMIUM_USD'),
            'amount' => '25.00',
            'amount_usd' => '29.00',
            'audience' => 'business',
            'tier' => 'premium',
        ],
        'clinic-triage' => [
            'type' => 'clinic',
            'price_id' => env('STRIPE_PRICE_CLINIC'),
            'price_id_usd' => env('STRIPE_PRICE_CLINIC_USD'),
            'amount' => '99.00',
            'amount_usd' => '129.00',
            'audience' => 'clinic',
        ],
    ],
];
