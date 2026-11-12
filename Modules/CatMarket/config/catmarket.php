<?php

declare(strict_types=1);

return [
    'site_name' => env('SITE_NAME', env('APP_NAME', 'Cat Marketplace')),
    'site_tagline' => env('SITE_TAGLINE', 'Find cats and kittens for sale or adoption in the UK and US. Rehome a cat, buy cat products, and find vets, cat sitters and catteries near you.'),
    'amazon' => ['uk' => env('AMAZON_TAG_UK'), 'us' => env('AMAZON_TAG_US')],
    'images' => [
        'hero' => 'https://www.figma.com/api/mcp/asset/58ab2cac-6d73-4c1a-adef-066832c0988a.png',
        'kittens' => 'https://www.figma.com/api/mcp/asset/136c4b45-544e-4d9a-b3f4-0cac9f9e326f.png',
        'services' => 'https://www.figma.com/api/mcp/asset/fcb7196b-c397-459d-abad-118b0a943347.png',
        'rehoming' => 'https://www.figma.com/api/mcp/asset/0d971574-13dd-4295-91dd-e3b3f6552ff1.png',
        'products' => 'https://www.figma.com/api/mcp/asset/ca33e1b4-366f-4e7a-86d6-2809ef3432c3.png',
        'pedigree' => 'https://www.figma.com/api/mcp/asset/e8ef7044-2887-4757-8915-26ea36ccbfe1.png',
        'lost-found' => 'https://www.figma.com/api/mcp/asset/5df6693a-675b-4b15-aa08-eeae72941350.png',
    ],
    'category_images' => [
        'cats-for-rehoming' => 'rehoming',
        'kittens' => 'kittens',
        'pedigree-cats' => 'pedigree',
        'lost-and-found' => 'lost-found',
        'cat-products' => 'products',
        'cat-services' => 'services',
    ],
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
