<?php

declare(strict_types=1);

return [
    'types' => [
        'vets' => ['name' => 'Vets', 'singular' => 'Vet'],
        'cat-sitters' => ['name' => 'Pet sitters & dog walkers', 'singular' => 'Pet sitter'],
        'groomers' => ['name' => 'Groomers', 'singular' => 'Groomer'],
        'catteries' => ['name' => 'Kennels & catteries', 'singular' => 'Kennels or cattery'],
        'behaviourists' => ['name' => 'Behaviourists', 'singular' => 'Behaviourist'],
        'rescues' => ['name' => 'Rescues & charities', 'singular' => 'Rescue'],
        'pet-shops' => ['name' => 'Pet shops', 'singular' => 'Pet shop'],
        'pet-transport' => ['name' => 'Pet transport', 'singular' => 'Pet transport'],
    ],
    'per_page' => 20,
    'outreach' => [
        'enabled' => (bool) env('OUTREACH_ENABLED', false),
        'daily_limit' => (int) env('OUTREACH_DAILY_LIMIT', 30),
        'mailer' => env('OUTREACH_MAILER', 'outreach'),
        'from_address' => env('OUTREACH_FROM_ADDRESS'),
        'from_name' => env('OUTREACH_FROM_NAME', env('SITE_NAME', 'Petavilo')),
        'sender_name' => env('OUTREACH_SENDER_NAME', ''),
        'countries' => array_values(array_filter(array_map('trim', explode(',', (string) env('OUTREACH_COUNTRIES', 'US,GB'))))),
        'uk_types' => ['vets', 'rescues', 'pet-shops'],
        'follow_up_after_days' => 14,
        'max_emails' => 2,
        'role_prefixes' => [
            'info', 'hello', 'enquiries', 'enquiry', 'inquiries', 'contact', 'reception', 'admin', 'office', 'mail',
            'bookings', 'booking', 'appointments', 'frontdesk', 'team', 'clinic', 'practice', 'vets', 'vet', 'shop',
            'sales', 'support', 'help', 'care', 'adopt', 'adoptions', 'rehoming', 'general',
        ],
    ],
];
