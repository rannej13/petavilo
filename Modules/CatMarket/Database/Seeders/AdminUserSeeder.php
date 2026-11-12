<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $email = trim((string) config('catmarket.admin.email', ''));
        $password = (string) config('catmarket.admin.password', '');

        if ($email === '' || $password === '') {
            $this->command?->warn('ADMIN_EMAIL or ADMIN_PASSWORD missing: no admin user created.');

            return;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            $user = User::query()->create([
                'name' => (string) config('catmarket.admin.name', 'Site admin'),
                'email' => $email,
                'password' => $password,
                'status' => 'active',
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->assignRole($role);
    }
}
