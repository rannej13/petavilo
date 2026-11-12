<?php

declare(strict_types=1);

namespace Modules\Directory\Models;

use App\Support\MembershipDirectory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\App\Models\User;

class BusinessClaim extends Model
{
    use SoftDeletes;

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    protected $fillable = ['business_id', 'user_id', 'role_at_business', 'phone', 'message', 'status', 'decided_at'];

    protected $casts = ['decided_at' => 'datetime'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hasPending(int $businessId, int $userId): bool
    {
        return static::query()->where('business_id', $businessId)->where('user_id', $userId)->where('status', self::PENDING)->exists();
    }

    public function approve(): void
    {
        $business = $this->business;

        if (! $business instanceof Business) {
            return;
        }

        $userId = (int) $this->getAttribute('user_id');

        $business->forceFill([
            'owner_user_id' => $userId,
            'claimed_at' => now(),
            'is_verified' => true,
        ])->save();

        Business::syncTierForOwner($userId, MembershipDirectory::directoryTierFor($userId));

        $this->forceFill(['status' => self::APPROVED, 'decided_at' => now()])->save();

        static::query()
            ->where('business_id', $business->getKey())
            ->whereKeyNot($this->getKey())
            ->where('status', self::PENDING)
            ->update(['status' => self::REJECTED, 'decided_at' => now()]);
    }

    public function reject(): void
    {
        $this->forceFill(['status' => self::REJECTED, 'decided_at' => now()])->save();
    }
}
