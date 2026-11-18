<?php

declare(strict_types=1);

namespace Modules\Tv\Models;

use Illuminate\Database\Eloquent\Model;

class PetOfWeekVote extends Model
{
    protected $fillable = ['entry_id', 'user_id', 'week_start'];

    protected $casts = ['week_start' => 'date'];
}
