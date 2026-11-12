<?php

declare(strict_types=1);

namespace Modules\Directory\Models;

use Illuminate\Database\Eloquent\Model;

class DirectorySuppression extends Model
{
    protected $table = 'directory_suppressions';

    protected $fillable = ['email', 'reason'];

    public static function add(string $email, string $reason = 'unsubscribed'): void
    {
        $email = strtolower(trim($email));

        if ($email !== '') {
            static::query()->firstOrCreate(['email' => $email], ['reason' => $reason]);
        }
    }

    public static function contains(string $email): bool
    {
        return static::query()->where('email', strtolower(trim($email)))->exists();
    }
}
