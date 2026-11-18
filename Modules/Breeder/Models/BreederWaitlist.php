<?php

declare(strict_types=1);

namespace Modules\Breeder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreederWaitlist extends Model
{
    protected $table = 'breeder_waitlist';

    public const STATUSES = ['waiting' => 'Waiting', 'offered' => 'Offered', 'reserved' => 'Reserved', 'withdrawn' => 'Withdrawn'];

    protected $fillable = ['litter_id', 'name', 'email', 'phone', 'town', 'preference', 'status', 'notes'];

    public function litter(): BelongsTo
    {
        return $this->belongsTo(BreederLitter::class, 'litter_id');
    }
}
