<?php

declare(strict_types=1);

namespace Modules\Guide\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Guide\Models\Guide;

class GuideSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $record) {
            Guide::query()->firstOrCreate(['slug' => $record['slug']], $record);
        }
    }

    private function records(): array
    {
        return [
            [
                'slug' => 'cat-insurance-types-of-cover-explained',
                'title' => 'Cat insurance explained (UK): the four types of cover',
                'type' => 'insurance',
                'excerpt' => 'Accident-only, time-limited, maximum benefit and lifetime cover: what each one pays for and what to check before you buy.',
                'is_published' => true,
                'reviewed_on' => now()->toDateString(),
                'body' => <<<'MD'
Pet insurance helps with unexpected vet bills. Policies differ mainly in how long they pay for an illness and how much they pay in total. Understanding the four main types makes it easier to compare quotes yourself.

## The four types of cover

| Type | How the limit works | Watch out for |
|---|---|---|
| Accident-only | Pays for injuries from accidents, not illness | No cover for illness at all |
| Time-limited | Pays for each condition for a set time, often 12 months | Long-term conditions stop being covered after the time limit |
| Maximum benefit | Pays up to a set amount per condition, with no time limit | Once the amount is used, that condition is no longer covered |
| Lifetime | Pays up to an annual limit that resets each year, for as long as you renew | Usually the most expensive; premiums often rise with age |

## Excess and co-payment

The excess is the amount you pay towards each claim. Some policies also add a co-payment, a percentage of each bill you pay yourself, often once a cat reaches a certain age.

## Pre-existing conditions

Most policies exclude conditions your cat had, or showed signs of, before cover started. Insure early, ideally while your cat is young and healthy.

## Waiting periods and age limits

Illness cover usually starts after a waiting period of a couple of weeks. Many insurers have a maximum age for starting a new policy, so check this for older cats.

## Questions to ask any insurer

- Is the limit per condition, per year or for life?
- What is the excess, and is there a co-payment?
- Are dental treatment, behavioural therapy and complementary treatment covered?
- What happens to premiums as my cat gets older?
- Will you pay the vet directly?

## Where to compare

Compare quotes directly with insurers or through a comparison site authorised by the Financial Conduct Authority. If you have a complaint an insurer cannot resolve, you can take it to the Financial Ombudsman Service.
MD,
            ],
            [
                'slug' => 'adopt-or-buy-where-to-get-a-cat-uk',
                'title' => 'Adopt or buy? Where to get your cat in the UK',
                'type' => 'buying',
                'excerpt' => 'Rescue centres, private rehoming, owners of the mother and registered breeders compared, with the checks to make for each.',
                'is_published' => true,
                'reviewed_on' => now()->toDateString(),
                'body' => <<<'MD'
There are four main places to get a cat in the UK. Each can be a good choice if you know what to check.

## Your options at a glance

| Where | What you usually get | Key check |
|---|---|---|
| Rescue or rehoming charity | Adult cats and kittens, usually neutered, vaccinated and microchipped | Ask about the cat's history and any home-check process |
| Private rehoming | Adult cats whose owners can no longer keep them | Meet the cat at home and ask for vet records |
| Owner of the mother | Non-pedigree kittens bred at home | See the kittens with their mother where they were born |
| Registered or licensed breeder | Pedigree kittens with registration papers | Check registration and, for licensed sellers, the council licence |

## Rescue and rehoming charities

Charities match cats to homes and usually neuter, vaccinate and microchip before rehoming. Many adult cats need homes, and rescues will tell you about each cat's personality.

## Private rehoming

Owners sometimes need to rehome a cat because of a move, allergies or a change in circumstances. Ask why the cat is being rehomed, how it gets on with children and other pets, and for its vaccination and microchip records.

## Kittens from the owner of the mother

Always see the kittens with their mother at the home where they were born. Kittens should be at least 8 weeks old before leaving, and many charities recommend 12 to 13 weeks.

## Registered and licensed breeders

Pedigree breeders often register kittens with the GCCF or TICA. Anyone selling kittens as a business, or kittens they did not breed, needs a licence from their council and must show the licence number in adverts.

## Red flags for every option

- The seller offers to deliver or meet you halfway
- You are asked for a deposit before you have seen the cat
- The kittens are not with their mother, or the mother is "out"
- There is no vaccination or microchip record
- The same photos appear on other adverts

Read our [buying safely checklist](/pages/safety-tips) before you visit.
MD,
            ],
            [
                'slug' => 'best-cat-carriers',
                'title' => 'Best cat carriers: picks for vet trips, travel and multi-cat homes',
                'type' => 'comparison',
                'excerpt' => 'TEMPLATE: replace the placeholders with products you have checked, then publish.',
                'is_published' => false,
                'has_affiliate_links' => true,
                'body' => <<<'MD'
Summary: our three picks in one sentence each. [Replace this paragraph.]

| Product | Best for | Key spec | Price range | Check price |
|---|---|---|---|---|
| [Product 1] | Vet trips | Top-loading, hard shell | £–££ | [Link](https://www.amazon.co.uk/) |
| [Product 2] | Car travel | Seatbelt loop, ventilation | £–££ | [Link](https://www.amazon.co.uk/) |
| [Product 3] | Large cats | Fits cats up to [x] kg | ££ | [Link](https://www.amazon.co.uk/) |

## How we chose

Explain your criteria: ease of getting a cat in and out, ventilation, cleaning, sturdiness and size. Say where your information comes from.

## [Product 1]: best for vet trips

Who it suits, pros, cons.

## [Product 2]: best for car travel

Who it suits, pros, cons.

## [Product 3]: best for large cats

Who it suits, pros, cons.

## What to look for in a cat carrier

Top opening, secure latches, a removable top so the vet can examine your cat inside, washable base.

## FAQs

Answer three to five real questions people search for.
MD,
            ],
            [
                'slug' => 'new-kitten-checklist',
                'title' => 'New kitten checklist: everything to buy before day one',
                'type' => 'care',
                'excerpt' => 'DRAFT: check every item and add your affiliate links, then publish.',
                'is_published' => false,
                'body' => <<<'MD'
Get these ready before your kitten comes home, so the first days are calm.

## Essentials

- Carrier for the journey home
- Litter tray and the same type of litter the kitten already uses
- Food the kitten is already eating, to avoid upset stomachs
- Food and water bowls, kept away from the litter tray
- A bed or blanket in a quiet corner
- Scratching post
- A few simple toys

## Health

- Register with a vet and book the first check
- Ask about vaccinations, flea and worm treatment and neutering
- Check the microchip is registered in your name

## Kitten-proofing

Block gaps behind appliances, tidy away cables and toxic plants, and keep toilet lids down.
MD,
            ],
            [
                'slug' => 'maine-coon-breed-guide',
                'title' => 'Maine Coon: breed guide',
                'type' => 'breed',
                'excerpt' => 'DRAFT outline for a breed page. Write each section, then publish.',
                'is_published' => false,
                'body' => <<<'MD'
## At a glance

| | |
|---|---|
| Size | [ ] |
| Coat | [ ] |
| Temperament | [ ] |
| Grooming | [ ] |
| Lifespan | [ ] |

## Personality

## Care and grooming

## Health: what to ask a breeder

## Is a Maine Coon right for you?

## Finding a Maine Coon

Link to the Pedigree Cats category and the buying safely checklist.
MD,
            ],
        ];
    }
}
