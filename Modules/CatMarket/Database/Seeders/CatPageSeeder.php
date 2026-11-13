<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Page\Models\Page;

class CatPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $record) {
            Page::query()->firstOrCreate(['slug' => $record['slug']], $record + ['is_published' => true]);
        }

        Page::query()->whereNotIn('slug', array_column($this->records(), 'slug'))->update(['is_published' => false]);

        \Modules\CatMarket\Support\ContactEmail::sync();

        $about = Page::query()->where('slug', 'about')->first();

        if ($about instanceof Page && str_contains((string) $about->getAttribute('body'), "\n\nWho we are\n")) {
            $about->update(['body' => trim(strstr((string) $about->getAttribute('body'), "\n\nWho we are\n", true))]);
        }
    }

    private function records(): array
    {
        $site = (string) config('catmarket.site_name');
        $company = (array) config('catmarket.company', []);
        $legalName = filled($company['name'] ?? null) ? (string) $company['name'] : '[Company name] Ltd';
        $number = filled($company['number'] ?? null) ? (string) $company['number'] : '[company number]';
        $address = filled($company['address'] ?? null) ? (string) $company['address'] : '[registered office address]';
        $email = filled($company['email'] ?? null) ? (string) $company['email'] : '[contact email]';
        $icoLine = filled($company['ico_number'] ?? null) ? ' ICO registration number: '.(string) $company['ico_number'].'.' : '';
        $jurisdiction = filled($company['jurisdiction'] ?? null) ? (string) $company['jurisdiction'] : 'Scotland';
        $showCompany = (bool) config('catmarket.show_company_details', false);
        $operator = $showCompany
            ? "{$site} is run by {$legalName}, a company registered in {$jurisdiction} (company number {$number}). Registered office: {$address}."
            : "{$site} is run by a company registered in {$jurisdiction}, United Kingdom.";
        $controller = $showCompany ? $legalName : "The company that runs {$site}";

        return [
            [
                'slug' => 'about',
                'title' => 'About us',
                'excerpt' => "A marketplace for pets in the UK and US, built around welfare.",
                'placement' => Page::PLACEMENT_FOOTER,
                'sort_order' => 1,
                'meta_description' => "{$site}: pets for sale and rehoming, verified rescues, lost and found, pet supplies and trusted pet services across the UK and US.",
                'body' => "Why we exist\n{$site} helps pets find good homes and helps owners find the products and people they need. Cats, dogs, rabbits, small animals, birds, reptiles and fish all have a place here. We care more about the right home than a quick sale.\n\nRescue first\nVerified rescues and charities can list animals for adoption, and anyone who needs to rehome a pet can do it here for free or with a rescue's help. Lost & Found is always free.\n\nHow we keep it safe\nWe review animal adverts and act quickly on anything reported. We follow the Pet Advertising Advisory Group (PAAG) standards, show licence numbers for licensed sellers and breeders, and ask for microchip, vaccination and health record details on every animal advert. Unknown stays unknown: we never guess health details.\n\nHow we make money\nRehoming adverts and Lost & Found are free. We earn from optional featured adverts, memberships for breeders, rescues and businesses, advertising and affiliate links. None of these change whether an advert is approved, and rescue animals are never ranked by payment.",
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact us',
                'excerpt' => 'Get in touch with the team.',
                'placement' => Page::PLACEMENT_FOOTER,
                'sort_order' => 2,
                'meta_description' => "Contact {$site}.",
                'body' => "Email\nWrite to {$email}. We reply within two working days.\n\nReport an advert\nUse the Report button on any advert or profile. It reaches our moderators straight away and is the fastest way to flag a welfare concern.\n\nAnimal welfare emergencies in the UK\nIf an animal is in immediate danger, call the RSPCA on 0300 1234 999 (England and Wales), the Scottish SPCA on 03000 999 999, or the USPCA on 028 3025 1000 (Northern Ireland).\n\nAnimal welfare emergencies in the US\nContact your local animal control agency or police. For a suspected poisoning, call the ASPCA Animal Poison Control Center on (888) 426-4435.",
            ],
            [
                'slug' => 'how-it-works',
                'title' => 'How it works',
                'excerpt' => 'Post, check, connect.',
                'placement' => Page::PLACEMENT_HELP,
                'sort_order' => 1,
                'meta_description' => "How to rehome a pet, advertise a litter, report a lost pet or find a pet on {$site}.",
                'body' => "Create your advert\nType a short title, pick the suggested category, then fill in the pet details: age, vaccinations, microchip, health records and who is selling or rehoming. Licensed sellers and breeders must add their licence number.\n\nIt goes live\nYour advert appears straight away. We review animal adverts and may contact you or remove an advert that breaks our rules.\n\nTalk to people\nBuyers and adopters message you through the site. Keep conversations here so we can help if something goes wrong.\n\nMeet the pet\nBuyers should always see puppies and kittens with their mother at the place they were born. Never hand over money before you have met the pet.",
            ],
            [
                'slug' => 'safety-tips',
                'title' => 'Buying or adopting safely',
                'excerpt' => "How to avoid scams and poorly bred puppies and kittens.",
                'placement' => Page::PLACEMENT_HELP,
                'sort_order' => 2,
                'meta_description' => "Simple checks before you buy or adopt a pet in the UK or US.",
                'body' => "See them with their mother\nVisit the home where the puppies or kittens were born and see them with their mother. Be wary of anyone who offers to meet you halfway or deliver.\n\nCheck the age\nPuppies and kittens should not leave their mother before 8 weeks old. Many welfare charities recommend waiting until 12 to 13 weeks for kittens.\n\nCheck the age adds up\nIf the advert says 3 months old, the date of birth should say the same. We flag adverts where they do not match.\n\nAsk for the paperwork\nAsk for the vaccination record, microchip registration and, for pedigrees, registration papers. Licensed sellers must show their licence number, and you can check it with the council that issued it.\n\nNever pay a deposit before you visit\nScammers ask for deposits by bank transfer for pets that do not exist. Do not pay until you have met the pet in person.\n\nThink about adopting\nVerified rescues on {$site} assess their animals and support you after adoption.\n\nReport anything that worries you\nUse the Report button. You can also report concerning adverts to the Pet Advertising Advisory Group at paag.org.uk.",
            ],
            [
                'slug' => 'listing-rules',
                'title' => 'Advert rules',
                'excerpt' => 'What you can and cannot advertise.',
                'placement' => Page::PLACEMENT_HELP,
                'sort_order' => 3,
                'meta_description' => "Rules for adverts on {$site}, including PAAG standards for animal adverts.",
                'body' => "We review animal adverts\nAdverts go live straight away. We review animal adverts and may ask for more information or remove an advert that breaks these rules.\n\nPuppies and kittens in the UK\nYou may advertise puppies or kittens you bred yourself. If you breed or sell as a business, or sell animals you did not breed, you need a licence from your local council and must show the licence number and issuing council in the advert. Third-party sales of puppies and kittens under 6 months old are illegal in England, Wales and Scotland.\n\nOther pets in the UK\nSelling pets as a business, including rabbits, birds, small animals, reptiles and fish, needs a council pet-selling licence.\n\nThe US\nLaws differ by state. Most states do not allow puppies or kittens to be sold before 8 weeks old, and some breeders and dealers need a state or USDA licence. Follow your state and local rules and add any licence number to your advert.\n\nMinimum age\nPuppies and kittens should not be advertised as ready to leave before 8 weeks old.\n\nMicrochipping in the UK\nDogs must be microchipped and registered by 8 weeks old. In England cats must be microchipped by 20 weeks old. Never post a microchip number in an advert.\n\nPhotos and description\nUse recent photos of the actual animal. Describe its health honestly. If you do not know something, choose Unknown.\n\nNot allowed\nWild and dangerous wild animals, primates, wild-cat hybrids such as servals and caracals, dogs of banned types in the UK including XL Bully types, animals with cropped ears or other mutilations, animals offered in exchange for goods, sick or injured animals without a vet's advice, pregnant animals offered for sale, and delivery or courier of animals bought unseen.\n\nPayments\nWe do not take payment for animals. Never ask a buyer to pay a deposit before they have seen the animal.\n\nStandards we follow\nFor UK adverts these rules follow the Pet Advertising Advisory Group (PAAG) Advertising Standards and the animal licensing regulations of England, Wales, Scotland and Northern Ireland. US adverts must follow the law of the state where the animal is.",
            ],
            [
                'slug' => 'affiliate-disclosure',
                'title' => 'How we make money',
                'excerpt' => 'Affiliate links, sponsorships and ads, explained.',
                'placement' => Page::PLACEMENT_LEGAL,
                'sort_order' => 4,
                'meta_description' => "How {$site} earns money from affiliate links, advertising and sponsorships.",
                'body' => "Affiliate links\nSome guides contain affiliate links to shops such as Amazon. If you buy through one, we may earn a small commission at no extra cost to you. As an Amazon Associate we earn from qualifying purchases. We disclose these links in line with UK advertising rules and the US Federal Trade Commission's guidance on endorsements.\n\nOur picks\nWe choose products on their merits. Commission never decides what we recommend, and we never put affiliate links on adverts for animals.\n\nSponsored content\nAnything paid for by a brand is labelled Sponsored or Ad.\n\nAdvertising\nWe show ads from Google AdSense. You can choose whether ads are personalised through the consent message and your Google ad settings.\n\nPet insurance\nOur insurance pages are general information only. We are not authorised by the Financial Conduct Authority, and we are not a licensed insurance producer in any US state. We do not give advice on, arrange or recommend particular policies.",
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms of use',
                'excerpt' => 'The rules for using the site.',
                'placement' => Page::PLACEMENT_LEGAL,
                'sort_order' => 1,
                'meta_description' => "Terms of use for {$site}.",
                'body' => "Who we are\n{$operator} Contact us at {$email}.\n\nWhere we operate\nThe site serves the United Kingdom and the United States. Adverts must follow the law of the country, and for the US the state, where the animal or item is.\n\nWhat we do\nWe provide a place to advertise pets, pet products and pet services, and for rescues to list animals for adoption. We are not a party to any agreement between buyers and sellers, and we do not own, inspect or sell any animal or item advertised.\n\nYour account\nYou must be 18 or over to post adverts. Keep your login details private. You are responsible for what is posted from your account.\n\nYour adverts\nAdverts must be accurate, legal and follow our Advert rules. We may edit, reject or remove any advert and suspend accounts that break these terms or put animals or people at risk.\n\nPaid features\nFeatured adverts and memberships are paid in advance through Stripe, in pounds for UK adverts and dollars for US adverts. Memberships renew monthly until you cancel in your account. If a paid feature does not start because of a fault on our side, we will refund it. Your statutory rights are not affected.\n\nDonations to rescues\nDonations go straight to the verified rescue's own Stripe account. We do not hold the money and take no commission; Stripe charges the rescue its normal card fee. The rescue is responsible for receipts, Gift Aid and refunds. Donating has no effect on how animals are shown on the site.\n\nContent you upload\nYou keep ownership of your photos and text and give us permission to show them on the site and in our marketing of your advert.\n\nOur liability\nWe do not exclude liability for death or personal injury caused by our negligence, for fraud, or for anything that cannot be excluded by law. Otherwise we are not liable for losses arising from dealings between users.\n\nLaw\nThese terms are governed by the law of {$jurisdiction}. If you live elsewhere in the UK, you can also bring proceedings in the courts where you live.",
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy policy',
                'excerpt' => 'How we collect and use your personal data.',
                'placement' => Page::PLACEMENT_LEGAL,
                'sort_order' => 2,
                'meta_description' => "Privacy policy for {$site}.",
                'body' => "Who is responsible\n{$controller} is the data controller for personal data collected through {$site}.{$icoLine} Contact: {$email}.\n\nWhat we collect\nAccount details (name, email, phone), the content of your adverts and messages, payment records (card details are handled by Stripe, not us), device and usage data, and cookie choices.\n\nWhy we use it and our legal basis\nTo run your account and adverts (contract); to keep the site safe, check adverts and prevent fraud (legitimate interests and legal obligations); to process payments and keep accounting records (contract and legal obligation); to show advertising and measure it (consent, through our consent message).\n\nWho we share it with\nStripe for payments, our hosting provider, our email provider, and Google for advertising where you have consented. We may share information with the police, local councils or animal welfare organisations where the law requires it or an animal is at risk.\n\nAdvertising and cookies\nGoogle and its partners use cookies to serve ads based on your visits to this and other websites, if you allow this in our consent message. You can change your choice at any time from the Privacy settings link, and manage Google ad personalisation at adssettings.google.com.\n\nHow long we keep it\nAccount data while your account is open and for up to 2 years after. Messages for up to 2 years. Payment records for 6 years for tax purposes.\n\nYour rights in the UK\nYou can ask to access, correct, delete, restrict or move your data, and object to how we use it. Email {$email}. You can complain to the Information Commissioner's Office at ico.org.uk.\n\nYour rights in the US\nDepending on your state, including California, you may have the right to know what personal information we collect, to correct or delete it, and to opt out of its sale or sharing for targeted advertising. We do not sell personal information for money. Advertising cookies may count as sharing: you can opt out using the Privacy settings link or a Global Privacy Control signal from your browser. We will not treat you differently for using these rights. Email {$email} to make a request.\n\nChildren\nThe site is not for children under 13 and we do not knowingly collect their data.",
            ],
            [
                'slug' => 'cookies',
                'title' => 'Cookie policy',
                'excerpt' => 'The cookies we use and how to control them.',
                'placement' => Page::PLACEMENT_LEGAL,
                'sort_order' => 3,
                'meta_description' => "Cookie policy for {$site}.",
                'body' => "Strictly necessary cookies\nThese keep you logged in, protect forms from misuse and remember your consent choice. The site cannot work without them.\n\nAdvertising cookies\nGoogle AdSense uses cookies to show and measure ads, and to personalise them if you agree. We ask for your consent through a consent message before these are set for visitors in the UK and EEA.\n\nPayment cookies\nStripe sets cookies on its checkout pages to prevent fraud.\n\nChanging your choice\nUse the Privacy settings link at the bottom of any page, or clear cookies in your browser.",
            ],
        ];
    }
}
