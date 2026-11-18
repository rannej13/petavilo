<?php

declare(strict_types=1);

namespace Modules\Tv\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\User\App\Models\User;

class PetOfWeekEntry extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WINNER = 'winner';

    protected $fillable = ['pet_name', 'species', 'caption', 'consent_feature'];

    protected $casts = [
        'week_start' => 'date',
        'consent_feature' => 'boolean',
        'decided_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Waiting for a check',
            self::STATUS_APPROVED => 'In the running',
            self::STATUS_REJECTED => 'Not accepted',
            self::STATUS_WINNER => 'Pet of the Week',
        ];
    }

    public static function currentWeek(): Carbon
    {
        return now()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photoUrl(): string
    {
        return Storage::disk('public')->url((string) $this->getAttribute('photo_path'));
    }

    public function ownerFirstName(): string
    {
        $name = trim((string) ($this->user?->getAttribute('name') ?? ''));

        return $name === '' ? 'A member' : (string) strtok($name, ' ');
    }
}
