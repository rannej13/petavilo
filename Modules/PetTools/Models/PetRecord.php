<?php

declare(strict_types=1);

namespace Modules\PetTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetRecord extends Model
{
    use SoftDeletes;

    public const TYPE_VACCINATION = 'vaccination';

    public const TYPE_PARASITE = 'parasite';

    public const TYPE_VET_VISIT = 'vet_visit';

    public const TYPE_MEDICATION = 'medication';

    public const TYPE_WEIGHT = 'weight';

    public const TYPE_GROOMING = 'grooming';

    public const TYPE_CLAIM = 'claim';

    public const TYPE_OTHER = 'other';

    protected $fillable = ['type', 'title', 'happened_on', 'due_on', 'value', 'notes'];

    protected $casts = [
        'happened_on' => 'date',
        'due_on' => 'date',
        'reminded_at' => 'datetime',
        'value' => 'decimal:2',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_VACCINATION => 'Vaccination',
            self::TYPE_PARASITE => 'Flea or worm treatment',
            self::TYPE_VET_VISIT => 'Vet visit',
            self::TYPE_MEDICATION => 'Medication',
            self::TYPE_WEIGHT => 'Weight (kg)',
            self::TYPE_GROOMING => 'Grooming or nails',
            self::TYPE_CLAIM => 'Insurance claim',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function scopeDueWithin(Builder $query, int $days): Builder
    {
        return $query->whereNotNull('due_on')
            ->whereDate('due_on', '<=', now()->addDays($days)->toDateString())
            ->whereNull('reminded_at');
    }

    public function typeLabel(): string
    {
        return self::types()[(string) $this->getAttribute('type')] ?? (string) $this->getAttribute('type');
    }

    public function isOverdue(): bool
    {
        $due = $this->getAttribute('due_on');

        return $due !== null && $due->isPast() && ! $due->isToday();
    }
}
