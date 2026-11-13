<?php

declare(strict_types=1);

namespace Modules\PetTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\User\App\Models\User;

class Pet extends Model
{
    use SoftDeletes;

    public const PASS_FIELDS = [
        'owner_phone' => 'Your phone number',
        'emergency_contact' => 'Emergency contact',
        'vet' => 'Vet name and phone',
        'medical' => 'Medical notes, allergies and medicines',
        'diet' => 'Diet',
        'insurance' => 'Insurer and claims phone (not the policy number)',
    ];

    protected $fillable = [
        'name', 'species', 'breed', 'sex', 'date_of_birth', 'colour', 'neutered',
        'microchip_number', 'microchip_database', 'medical_notes', 'allergies', 'medications', 'diet',
        'vet_name', 'vet_phone', 'insurer', 'policy_number', 'policy_type', 'policy_renewal',
        'policy_excess', 'policy_limit', 'insurer_claims_phone', 'owner_phone', 'emergency_contact', 'emergency_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'policy_renewal' => 'date',
        'microchip_number' => 'encrypted',
        'policy_number' => 'encrypted',
        'pass_fields' => 'array',
        'pass_enabled' => 'boolean',
        'is_missing' => 'boolean',
    ];

    public static function speciesOptions(): array
    {
        return [
            'cat' => 'Cat', 'dog' => 'Dog', 'rabbit' => 'Rabbit', 'guinea-pig' => 'Guinea pig',
            'small' => 'Other small pet', 'bird' => 'Bird', 'reptile' => 'Reptile', 'fish' => 'Fish', 'other' => 'Other',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(PetRecord::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public static function forOwner(int $userId): \Illuminate\Support\Collection
    {
        return static::query()->ownedBy($userId)->with(['records' => static fn ($query) => $query->orderByDesc('happened_on')])->orderBy('name')->get();
    }

    public static function findByPass(string $token): ?self
    {
        return static::query()->where('pass_token', $token)->where('pass_enabled', true)->first();
    }

    public function photoUrl(): ?string
    {
        $path = (string) $this->getAttribute('photo_path');

        return $path === '' ? null : Storage::disk('public')->url($path);
    }

    public function storePhoto(\Illuminate\Http\UploadedFile $file): void
    {
        $old = (string) $this->getAttribute('photo_path');
        $path = $file->store('pets', 'public');

        if ($old !== '') {
            Storage::disk('public')->delete($old);
        }

        $this->forceFill(['photo_path' => $path])->save();
    }

    public function enablePass(array $fields): void
    {
        $this->forceFill([
            'pass_token' => $this->getAttribute('pass_token') ?: Str::random(32),
            'pass_enabled' => true,
            'pass_fields' => array_values(array_intersect($fields, array_keys(self::PASS_FIELDS))),
        ])->save();
    }

    public function disablePass(): void
    {
        $this->forceFill(['pass_enabled' => false])->save();
    }

    public function resetPass(): void
    {
        $this->forceFill(['pass_token' => Str::random(32)])->save();
    }

    public function shows(string $field): bool
    {
        return in_array($field, (array) ($this->getAttribute('pass_fields') ?? []), true);
    }

    public function ageLabel(): ?string
    {
        $birth = $this->getAttribute('date_of_birth');

        if (! $birth instanceof Carbon) {
            return null;
        }

        $months = (int) $birth->diffInMonths(now());

        return $months < 24 ? $months.' '.Str::plural('month', $months) : intdiv($months, 12).' years';
    }

    public function speciesLabel(): string
    {
        return self::speciesOptions()[(string) $this->getAttribute('species')] ?? (string) $this->getAttribute('species');
    }

    public function maskedMicrochip(): ?string
    {
        $number = (string) ($this->getAttribute('microchip_number') ?? '');

        return $number === '' ? null : str_repeat('•', max(0, strlen($number) - 4)).substr($number, -4);
    }

    public function dueRecords(): \Illuminate\Support\Collection
    {
        return $this->records
            ->filter(static fn (PetRecord $record): bool => $record->getAttribute('due_on') !== null)
            ->sortBy('due_on')
            ->values();
    }

    public function latestWeight(): ?PetRecord
    {
        return $this->records->where('type', PetRecord::TYPE_WEIGHT)->sortByDesc('happened_on')->first();
    }
}
