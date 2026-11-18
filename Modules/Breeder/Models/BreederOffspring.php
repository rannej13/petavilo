<?php

declare(strict_types=1);

namespace Modules\Breeder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreederOffspring extends Model
{
    protected $table = 'breeder_offspring';

    public const STATUSES = [
        'available' => 'Available',
        'reserved' => 'Reserved',
        'sold' => 'Gone home',
        'keeping' => 'Keeping',
        'died' => 'Died',
    ];

    protected $fillable = ['name', 'sex', 'colour', 'microchip_number', 'status', 'buyer_name', 'buyer_email', 'buyer_town', 'care_notes'];

    protected $casts = [
        'microchip_number' => 'encrypted',
        'reserved_at' => 'datetime',
        'sold_at' => 'datetime',
        'handover_claimed_at' => 'datetime',
    ];

    public function litter(): BelongsTo
    {
        return $this->belongsTo(BreederLitter::class, 'litter_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(BreederRecord::class, 'offspring_id')->orderBy('recorded_on')->orderBy('id');
    }

    /**
     * @return list<array{date: string, grams: int}>
     */
    public function weights(): array
    {
        $records = $this->relationLoaded('records') ? $this->getRelation('records') : $this->records()->get();

        return $records->where('kind', 'weight')->filter(static fn ($r) => $r->getAttribute('grams') !== null)
            ->map(static fn ($r): array => ['date' => $r->getAttribute('recorded_on')->toDateString(), 'grams' => (int) $r->getAttribute('grams')])
            ->values()->all();
    }
}
