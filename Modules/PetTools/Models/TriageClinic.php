<?php

declare(strict_types=1);

namespace Modules\PetTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\User\App\Models\User;
use Throwable;

class TriageClinic extends Model
{
    use SoftDeletes;

    public const TRIAL_DAYS = 14;

    protected $fillable = ['name', 'email', 'phone', 'booking_url', 'emergency_text', 'country'];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'is_suspended' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TriageSubmission::class, 'clinic_id');
    }

    public static function forUser(int $userId): ?self
    {
        return static::query()->where('user_id', $userId)->first();
    }

    public static function findByToken(string $token): ?self
    {
        return static::query()->where('token', $token)->first();
    }

    public static function start(User $user, array $data): self
    {
        $clinic = new static();
        $clinic->fill($data);
        $clinic->forceFill([
            'user_id' => $user->getKey(),
            'token' => Str::random(32),
            'trial_ends_at' => now()->addDays(self::TRIAL_DAYS),
        ])->save();

        return $clinic;
    }

    public function onTrial(): bool
    {
        return $this->getAttribute('trial_ends_at') !== null && $this->getAttribute('trial_ends_at')->isFuture();
    }

    public function isSubscribed(): bool
    {
        try {
            return (bool) $this->user?->subscribed('clinic');
        } catch (Throwable) {
            return false;
        }
    }

    public function isLive(): bool
    {
        return ! $this->getAttribute('is_suspended') && ($this->onTrial() || $this->isSubscribed());
    }

    public function embedCode(): string
    {
        $url = route('pettools.widget', $this->getAttribute('token'));

        return '<iframe src="'.$url.'" title="Pet triage" style="width:100%;max-width:640px;height:1400px;border:0" loading="lazy"></iframe>';
    }
}
