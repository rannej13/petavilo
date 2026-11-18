<?php

declare(strict_types=1);

namespace Modules\PetTools\Models;

use Illuminate\Database\Eloquent\Model;

class PetMatchResult extends Model
{
    protected $fillable = ['user_id', 'answers', 'top_pet'];

    protected $casts = ['answers' => 'array'];

    /**
     * @return array<string, string>
     */
    public static function answersFor(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }

        $record = static::query()->where('user_id', $userId)->first();

        return $record === null ? [] : (array) $record->getAttribute('answers');
    }
}
