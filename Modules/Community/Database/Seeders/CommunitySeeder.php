<?php

declare(strict_types=1);

namespace Modules\Community\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Community\Models\Board;
use Modules\Community\Models\Thread;
use Modules\User\App\Models\User;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->boards() as $index => $board) {
            Board::query()->firstOrCreate(['slug' => $board['slug']], $board + ['sort_order' => $index + 1, 'is_active' => true]);
        }

        $admin = User::query()->where('email', (string) config('catmarket.admin.email'))->first();

        if (! $admin instanceof User || Thread::query()->withTrashed()->exists()) {
            return;
        }

        $site = (string) config('catmarket.site_name', 'Petavilo');

        foreach ($this->starters($site) as [$slug, $title, $body, $pinned]) {
            $board = Board::query()->where('slug', $slug)->first();

            if ($board instanceof Board) {
                Thread::query()->create([
                    'board_id' => $board->getKey(),
                    'user_id' => $admin->getKey(),
                    'title' => $title,
                    'body' => $body,
                    'is_pinned' => $pinned,
                ]);
            }
        }
    }

    private function boards(): array
    {
        return [
            ['name' => 'Introductions and cat photos', 'slug' => 'show-us-your-cat', 'image' => 'hero', 'description' => 'Say hello and show us your cats. Photos, names, funny habits and new arrivals.'],
            ['name' => 'Kittens and new cats', 'slug' => 'kittens-and-new-cats', 'image' => 'kittens', 'description' => 'Bringing a kitten or rescue cat home: litter training, settling in, vaccinations and first weeks.'],
            ['name' => 'Health and behaviour', 'slug' => 'health-and-behaviour', 'image' => 'guide-insurance', 'description' => 'Scratching, spraying, fussy eating, older cats and vet visits. Share what worked for you.'],
            ['name' => 'Breeds', 'slug' => 'breeds', 'image' => 'pedigree', 'description' => 'Maine Coons, Ragdolls, Bengals, British Shorthairs and every moggy in between.'],
            ['name' => 'Adoption and rescue', 'slug' => 'adoption-and-rescue', 'image' => 'rehoming', 'description' => 'Adopting, fostering, rescues you recommend and rehoming advice.'],
            ['name' => 'Products and food', 'slug' => 'products-and-food', 'image' => 'products', 'description' => 'Food, litter, cat trees, carriers and toys. What is worth buying and what is not.'],
            ['name' => 'Lost and found help', 'slug' => 'lost-and-found-help', 'image' => 'lost-found', 'description' => 'Tips for finding a missing cat and reuniting found cats. Post the cat itself as a free Lost & Found advert.'],
            ['name' => 'Vets, sitters and services', 'slug' => 'vets-and-services', 'image' => 'services', 'description' => 'Recommend a vet, cattery, sitter or groomer near you in the UK or US.'],
        ];
    }

    private function starters(string $site): array
    {
        return [
            ['show-us-your-cat', "Welcome to the {$site} community: read this first", "Hello and welcome!\n\nThis is a place for cat owners in the UK and US to swap advice, share photos and help each other.\n\nA few simple rules:\n- Be kind, everyone was a new cat owner once.\n- Do not sell or rehome animals in the forum. Post an advert instead, it is free to rehome.\n- Advice here never replaces a vet. If your cat is unwell, call your vet.\n- Use Report on anything that breaks the rules and our team will look at it.\n\nStart by introducing yourself and your cats below.", true],
            ['show-us-your-cat', 'Introduce yourself and your cats', "Tell us who you are, where you are (UK or US) and who you share your home with. Names, ages, breeds and any funny habits welcome.", false],
            ['kittens-and-new-cats', 'First week with a new kitten: what helped you most?', "If you have brought a kitten home recently, what made the first week easier? Litter, food, sleeping, meeting other pets? Share your tips for the next new owner.", false],
            ['health-and-behaviour', 'Indoor or outdoor cats: what do you do and why?', "In the UK most cats go outside, in the US most stay in. Which do you do, and what made you decide? Catios, harness walks and window perches all welcome.", false],
            ['products-and-food', 'Best cat tree or scratcher that actually lasted?', "Which cat tree or scratching post has stood up to your cats? And which fell apart in a month? Add where you bought it and roughly what it cost.", false],
            ['vets-and-services', 'Recommend a vet, cattery or sitter near you', "Had a great experience with a vet, cattery, groomer or sitter? Share the town and why you recommend them so others nearby can find them too.", false],
        ];
    }
}
