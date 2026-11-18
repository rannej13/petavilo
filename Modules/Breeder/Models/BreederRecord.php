<?php

declare(strict_types=1);

namespace Modules\Breeder\Models;

use Illuminate\Database\Eloquent\Model;

class BreederRecord extends Model
{
    public const KINDS = [
        'weight' => 'Weight',
        'vaccination' => 'Vaccination',
        'parasite' => 'Worming or flea treatment',
        'vet_check' => 'Vet check',
        'microchip' => 'Microchipped',
        'socialising' => 'Socialising',
        'note' => 'Note',
    ];

    protected $fillable = ['kind', 'recorded_on', 'grams', 'title', 'details'];

    protected $casts = ['recorded_on' => 'date'];

    public function label(): string
    {
        return (string) ($this->getAttribute('title') ?: (self::KINDS[$this->getAttribute('kind')] ?? 'Record'));
    }
}
