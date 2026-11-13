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

    protected $fillable = ['amount', 'fee_cover', 'tip', 'currency', 'donor_name', 'message', 'is_anonymous'];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_cover' => 'decimal:2',
        'tip' => 'decimal:2',
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
        return (float) $this->getAttribute('amount') + (float) $this->getAttribute('fee_cover') + (float) $this->getAttribute('tip');
    }

    public function tipLabel(): string
    {
        return self::money((float) $this->getAttribute('tip'), (string) $this->getAttribute('currency'));
    }

    public function feeCoverLabel(): string
    {
        return self::money((float) $this->getAttribute('fee_cover'), (string) $this->getAttribute('currency'));
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

        $this->sendReceipt();
        $this->notifyRescue();
    }

    private function sendReceipt(): void
    {
        $email = (string) $this->getAttribute('donor_email');
        $organisation = $this->organisation;

        if ($email === '' || $organisation === null) {
            return;
        }

        $site = (string) config('catmarket.site_name', 'Petavilo');
        $lines = [
            'Thank you for your donation to '.$organisation->getAttribute('name').'.',
            '',
            'Reference: D'.str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT),
            'Date: '.now()->isoFormat('D MMMM YYYY'),
            'Donation to the rescue: '.$this->amountLabel(),
        ];

        if ((float) $this->getAttribute('fee_cover') > 0) {
            $lines[] = 'Card fee you covered: '.$this->feeCoverLabel();
        }

        if ((float) $this->getAttribute('tip') > 0) {
            $lines[] = 'Your tip to '.$site.': '.$this->tipLabel();
        }

        $lines[] = 'Total paid: '.$this->totalLabel();

        if (filled($organisation->getAttribute('charity_number'))) {
            $lines[] = ($organisation->getAttribute('country') === 'US' ? 'EIN: ' : 'Charity number: ').$organisation->getAttribute('charity_number');
        }

        $lines[] = '';
        $lines[] = 'The full donation amount was passed to the rescue by Stripe. '.$site.' received only the tip shown above, if any.';

        try {
            \Illuminate\Support\Facades\Mail::raw(implode("\n", $lines), static function ($message) use ($email, $organisation): void {
                $message->to($email)->subject('Your donation to '.$organisation->getAttribute('name'));
            });
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function notifyRescue(): void
    {
        $organisation = $this->organisation;

        if ($organisation === null || $organisation->getAttribute('user_id') === null || ! class_exists(\Modules\Notification\Models\UserNotification::class)) {
            return;
        }

        $from = $this->getAttribute('is_anonymous') ? 'An anonymous supporter' : ((string) $this->getAttribute('donor_name') ?: 'A supporter');

        \Modules\Notification\Models\UserNotification::publish(
            (int) $organisation->getAttribute('user_id'),
            \Modules\Notification\Models\UserNotification::TYPE_PROMOTION,
            'New donation: '.$this->amountLabel(),
            $from.($this->getAttribute('message') ? ' wrote: '.$this->getAttribute('message') : ' donated.'),
            route('rescue.panel'),
        );
    }

    public static function feeCoverFor(float $amount, string $currency): float
    {
        $fee = (array) config('rescue.card_fee.'.$currency, ['percent' => 1.5, 'fixed' => 0.2]);
        $gross = ($amount + (float) $fee['fixed']) / (1 - ((float) $fee['percent'] / 100));

        return round($gross - $amount, 2);
    }

    public static function tipFor(float $amount, int $percent): float
    {
        return round($amount * max(0, min(30, $percent)) / 100, 2);
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
            'sum' => (float) (clone $paid)->sum('amount'),
            'currency' => $organisation->currency(),
        ];
    }
}
