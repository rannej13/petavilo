<?php

declare(strict_types=1);

namespace Modules\Breeder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreederAnimal extends Model
{
    protected $fillable = ['name', 'species', 'breed', 'sex', 'date_of_birth', 'microchip_number', 'registration', 'health_tests', 'notes', 'is_active'];

    protected $casts = [
        'date_of_birth' => 'date',
        'microchip_number' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function littersAsDam(): HasMany
    {
        return $this->hasMany(BreederLitter::class, 'dam_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(BreederRecord::class, 'animal_id')->orderByDesc('recorded_on');
    }

    public function ageLabel(): ?string
    {
        $dob = $this->getAttribute('date_of_birth');

        if ($dob === null) {
            return null;
        }

        $months = (int) $dob->diffInMonths(now());

        return $months < 24 ? $months.' months' : intdiv($months, 12).' years';
    }
}
