<?php

declare(strict_types=1);

namespace Modules\PetTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VetLead extends Model
{
    use SoftDeletes;

    public const KIND_DENTAL = 'dental';

    public const KIND_SENIOR = 'senior';

    protected $fillable = ['kind', 'country', 'town', 'name', 'email', 'phone', 'pet_name', 'answers', 'summary', 'status', 'admin_note'];

    protected $casts = [
        'answers' => 'array',
        'consented_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return ['new' => 'New', 'sent' => 'Sent to a vet', 'booked' => 'Booked', 'closed' => 'Closed'];
    }
}
