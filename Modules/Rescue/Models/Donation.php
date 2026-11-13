<?php

declare(strict_types=1);

namespace Modules\Rescue\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\App\Models\User;

class Donation extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    protected $table = 'rescue_donations';

    protected $fillable = ['amount', 'fee_cover', 'currency', 'donor_name', 'message', 'is_anonymous'];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_cover' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(RescueOrganisation::class, 'organisation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function total(): float
    {
        return (float) $this->getAttribute('amount') + (float) $this->getAttribute('fee_cover');
    }

    public function totalLabel(): string
    {
        return self::money($this->total(), (string) $this->getAttribute('currency'));
    }

    public function amountLabel(): string
    {
        return self::money((float) $this->getAttribute('amount'), (string) $this->getAttribute('currency'));
    }

    public function markPaid(?string $email, ?string $paymentIntent): void
    {
        if ($this->getAttribute('status') === self::STATUS_PAID) {
            return;
        }

        $this->forceFill([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'donor_email' => $email ?: $this->getAttribute('donor_email'),
            'payment_intent' => $paymentIntent,
        ])->save();
    }

    public static function feeCoverFor(float $amount, string $currency): float
    {
        $fee = (array) config('rescue.card_fee.'.$currency, ['percent' => 1.5, 'fixed' => 0.2]);
        $gross = ($amount + (float) $fee['fixed']) / (1 - ((float) $fee['percent'] / 100));

        return round($gross - $amount, 2);
    }

    public static function money(float $value, string $currency): string
    {
        return ($currency === 'USD' ? '$' : '£').number_format($value, 2);
    }

    public static function totalsFor(RescueOrganisation $organisation): array
    {
        $paid = static::query()->paid()->where('organisation_id', $organisation->getKey());

        return [
            'count' => (clone $paid)->count(),
            'sum' => (float) (clone $paid)->sum('amount') + (float) (clone $paid)->sum('fee_cover'),
            'currency' => $organisation->currency(),
        ];
    }
}
