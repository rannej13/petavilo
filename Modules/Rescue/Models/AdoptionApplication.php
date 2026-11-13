<?php

declare(strict_types=1);

namespace Modules\Rescue\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\App\Models\User;

class AdoptionApplication extends Model
{
    use SoftDeletes;

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_MORE_INFO = 'more_info';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = ['answers', 'message'];

    protected $casts = [
        'answers' => 'array',
        'history' => 'array',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_SUBMITTED => 'Sent',
            self::STATUS_REVIEWING => 'Being reviewed',
            self::STATUS_MORE_INFO => 'More details needed',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DECLINED => 'Not this time',
            self::STATUS_WITHDRAWN => 'Withdrawn',
        ];
    }

    public static function questions(): array
    {
        return [
            'home_type' => ['label' => 'Your home', 'options' => ['House with garden', 'House without garden', 'Flat with outdoor space', 'Flat without outdoor space', 'Other']],
            'tenure' => ['label' => 'Do you own or rent?', 'options' => ['Own', 'Rent, pets allowed in writing', 'Rent, need to ask the landlord', 'Live with family']],
            'children' => ['label' => 'Children at home', 'options' => ['None', 'Under 5', '5 to 11', '12 and over', 'Visit often']],
            'other_pets' => ['label' => 'Other pets at home', 'options' => ['None', 'Cats', 'Dogs', 'Cats and dogs', 'Other pets']],
            'hours_alone' => ['label' => 'Hours the pet would be alone on a typical day', 'options' => ['Under 2', '2 to 4', '4 to 6', 'Over 6']],
            'experience' => ['label' => 'Experience with this kind of pet', 'options' => ['First time', 'Some', 'Lots']],
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(RescueOrganisation::class, 'organisation_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function scopeForApplicant(Builder $query, int $userId): Builder
    {
        return $query->where('applicant_id', $userId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWING, self::STATUS_MORE_INFO]);
    }

    public static function submit(RescueOrganisation $organisation, int $listingId, int $applicantId, array $answers, string $message): self
    {
        $application = static::query()->open()
            ->where('listing_id', $listingId)
            ->where('applicant_id', $applicantId)
            ->first() ?? new static();

        $application->fill(['answers' => $answers, 'message' => $message]);
        $application->forceFill([
            'listing_id' => $listingId,
            'organisation_id' => $organisation->getKey(),
            'applicant_id' => $applicantId,
            'status' => self::STATUS_SUBMITTED,
        ]);
        $application->appendHistory(self::STATUS_SUBMITTED, $applicantId, null);
        $application->save();

        return $application;
    }

    public function moveTo(string $status, int $actorId, ?string $note): void
    {
        if (! array_key_exists($status, self::statuses())) {
            return;
        }

        $this->setAttribute('status', $status);
        $this->appendHistory($status, $actorId, $note);
        $this->save();
    }

    public function statusLabel(): string
    {
        return self::statuses()[(string) $this->getAttribute('status')] ?? (string) $this->getAttribute('status');
    }

    public function latestNote(): ?string
    {
        $notes = collect((array) ($this->getAttribute('history') ?? []))->pluck('note')->filter();

        return $notes->isEmpty() ? null : (string) $notes->last();
    }

    private function appendHistory(string $status, int $actorId, ?string $note): void
    {
        $history = (array) ($this->getAttribute('history') ?? []);
        $history[] = ['status' => $status, 'by' => $actorId, 'note' => $note, 'at' => now()->toIso8601String()];
        $this->setAttribute('history', $history);
    }
}
