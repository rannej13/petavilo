<?php

declare(strict_types=1);

namespace Modules\Breeder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreederLitter extends Model
{
    public const STATUSES = [
        'planned' => 'Planned',
        'expected' => 'Expected',
        'born' => 'Born',
        'ready' => 'Ready for new homes',
        'closed' => 'All homed',
    ];

    protected $fillable = ['name', 'species', 'breed', 'dam_id', 'sire_id', 'sire_name', 'mated_on', 'due_on', 'born_on', 'caesarean', 'born_count', 'ready_on', 'price', 'status', 'notes'];

    protected $casts = [
        'mated_on' => 'date',
        'due_on' => 'date',
        'born_on' => 'date',
        'ready_on' => 'date',
        'caesarean' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Typical gestation in days, used only to suggest a due date.
     */
    public const GESTATION_DAYS = ['dog' => 63, 'cat' => 65, 'rabbit' => 31, 'guinea-pig' => 68];

    /**
     * Minimum age to leave mother, used only to suggest a go-home date.
     */
    public const GO_HOME_WEEKS = ['dog' => 8, 'cat' => 9, 'rabbit' => 8, 'guinea-pig' => 4];

    public function dam(): BelongsTo
    {
        return $this->belongsTo(BreederAnimal::class, 'dam_id');
    }

    public function sire(): BelongsTo
    {
        return $this->belongsTo(BreederAnimal::class, 'sire_id');
    }

    public function offspring(): HasMany
    {
        return $this->hasMany(BreederOffspring::class, 'litter_id')->orderBy('id');
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(BreederWaitlist::class, 'litter_id')->orderBy('id');
    }

    public function sireLabel(): string
    {
        return (string) ($this->sire?->getAttribute('name') ?: $this->getAttribute('sire_name') ?: 'Not recorded');
    }

    public function ageWeeks(): ?int
    {
        $born = $this->getAttribute('born_on');

        return $born === null ? null : (int) $born->diffInWeeks(now());
    }

    public function counts(): array
    {
        $offspring = $this->relationLoaded('offspring') ? $this->getRelation('offspring') : $this->offspring()->get();

        return [
            'total' => $offspring->count(),
            'available' => $offspring->where('status', 'available')->count(),
            'reserved' => $offspring->where('status', 'reserved')->count(),
            'sold' => $offspring->where('status', 'sold')->count(),
        ];
    }
}
