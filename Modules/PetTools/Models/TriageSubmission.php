<?php

declare(strict_types=1);

namespace Modules\PetTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TriageSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = ['tier', 'owner_name', 'owner_email', 'owner_phone', 'pet_name', 'answers', 'soap'];

    protected $casts = [
        'answers' => 'array',
        'emailed_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(TriageClinic::class, 'clinic_id');
    }
}
