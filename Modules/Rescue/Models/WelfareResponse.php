<?php

declare(strict_types=1);

namespace Modules\Rescue\Models;

use Illuminate\Database\Eloquent\Model;

class WelfareResponse extends Model
{
    protected $fillable = ['campaign', 'answer', 'country', 'fingerprint'];

    public static function record(string $campaign, string $answer, ?string $country, string $fingerprint): void
    {
        static::query()->updateOrCreate(
            ['campaign' => $campaign, 'fingerprint' => $fingerprint],
            ['answer' => $answer, 'country' => $country],
        );
    }

    public static function results(string $campaign, array $answers): array
    {
        $counts = static::query()
            ->where('campaign', $campaign)
            ->selectRaw('answer, COUNT(*) as aggregate')
            ->groupBy('answer')
            ->pluck('aggregate', 'answer');
        $total = max(1, (int) $counts->sum());

        return collect($answers)->map(static fn (string $label, string $key): array => [
            'key' => $key,
            'label' => $label,
            'count' => (int) ($counts[$key] ?? 0),
            'percent' => (int) round(((int) ($counts[$key] ?? 0)) * 100 / $total),
        ])->values()->all();
    }

    public static function totalFor(string $campaign): int
    {
        return static::query()->where('campaign', $campaign)->count();
    }
}
