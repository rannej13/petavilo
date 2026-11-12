<?php

declare(strict_types=1);

return [
    'site_name' => env('SITE_NAME', env('APP_NAME', 'Cat Marketplace')),
    'site_tagline' => env('SITE_TAGLINE', 'Find cats and kittens for sale or adoption in the UK and US. Rehome a cat, buy cat products, and find vets, cat sitters and catteries near you.'),
    'amazon' => ['uk' => env('AMAZON_TAG_UK'), 'us' => env('AMAZON_TAG_US')],
    'forum_url' => env('FORUM_URL'),
    'show_company_details' => (bool) env('SHOW_COMPANY_DETAILS', false),
    'countries' => [
        'GB' => ['name' => 'United Kingdom', 'currency' => 'GBP', 'symbol' => '£'],
        'US' => ['name' => 'United States', 'currency' => 'USD', 'symbol' => '$'],
    ],
    'company' => [
        'name' => env('COMPANY_NAME', ''),
        'number' => env('COMPANY_NUMBER', ''),
        'jurisdiction' => env('COMPANY_JURISDICTION', 'Scotland'),
        'address' => env('COMPANY_ADDRESS', ''),
        'email' => env('CONTACT_EMAIL', ''),
        'ico_number' => env('ICO_REGISTRATION_NUMBER', ''),
    ],
    'admin' => [
        'name' => env('ADMIN_NAME', 'Site admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
    'enforce_rules' => (bool) env('WELFARE_ENFORCE_RULES', false),
    'premoderate' => (bool) env('WELFARE_PREMODERATE', false),
    'animal_roots' => ['cats-for-rehoming', 'kittens', 'pedigree-cats'],
    'min_leave_weeks' => 8,
    'microchip_weeks' => 20,
    'blocked_phrases' => [
        'serval', 'caracal', 'ocelot', 'bobcat', 'lynx', 'wildcat',
        'swap for', 'swap with', 'exchange for', 'part exchange',
        'courier delivery', 'we can ship', 'shipping available', 'can be shipped',
    ],
    'held_phrases' => [
        'deposit', 'bank transfer', 'paypal friends', 'western union', 'gift card',
        'delivery', 'courier', 'no viewings', 'cannot view', 'meet halfway',
        'f1', 'f2', 'fold', 'urgent rehome', 'free to good home', 'whatsapp only',
    ],
];
