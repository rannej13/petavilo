<?php

declare(strict_types=1);

namespace Modules\Breeder\Support;

use Modules\Breeder\Models\BreederLitter;

/**
 * Warnings based on the dog breeding licence conditions in England
 * (Animal Welfare (Licensing of Activities Involving Animals) (England)
 * Regulations 2018, Schedule 6, paragraph 3). Shown as warnings, not legal advice.
 */
final class BreedingChecks
{
    public const SOURCE_URL = 'https://www.legislation.gov.uk/uksi/2018/486/schedule/6';

    /**
     * @return list<string>
     */
    public static function forLitter(BreederLitter $litter): array
    {
        if ($litter->getAttribute('species') !== 'dog' || $litter->dam === null) {
            return [];
        }

        $dam = $litter->dam;
        $warnings = [];
        $mated = $litter->getAttribute('mated_on') ?? $litter->getAttribute('born_on')?->copy()->subDays(63);
        $dob = $dam->getAttribute('date_of_birth');

        if ($mated !== null && $dob !== null && $dob->copy()->addMonths(12)->gt($mated)) {
            $warnings[] = $dam->getAttribute('name').' was under 12 months old when mated. Licensed breeders must not mate a bitch under 12 months.';
        }

        $others = $dam->littersAsDam()->whereKeyNot($litter->getKey())->whereNotNull('born_on')->get();
        $born = $litter->getAttribute('born_on') ?? $litter->getAttribute('due_on');

        if ($born !== null) {
            foreach ($others as $other) {
                $otherBorn = $other->getAttribute('born_on');

                if ($otherBorn !== null && abs($otherBorn->diffInDays($born)) < 365) {
                    $warnings[] = 'This would be a second litter for '.$dam->getAttribute('name').' within 12 months. Licensed breeders may not have more than one litter per bitch in 12 months.';
                    break;
                }
            }
        }

        $total = $others->count() + 1;

        if ($total > 6) {
            $warnings[] = $dam->getAttribute('name').' would have had '.$total.' litters. The limit for licensed breeders is 6 in her lifetime.';
        }

        $caesareans = $others->where('caesarean', true)->count();

        if ($caesareans >= 2) {
            $warnings[] = $dam->getAttribute('name').' has had 2 litters by caesarean. Licensed breeders must not mate her again.';
        }

        return $warnings;
    }

    public static function suggestDue(string $species, ?\Carbon\CarbonInterface $mated): ?string
    {
        $days = BreederLitter::GESTATION_DAYS[$species] ?? null;

        return $days === null || $mated === null ? null : $mated->copy()->addDays($days)->toDateString();
    }

    public static function suggestReady(string $species, ?\Carbon\CarbonInterface $born): ?string
    {
        $weeks = BreederLitter::GO_HOME_WEEKS[$species] ?? null;

        return $weeks === null || $born === null ? null : $born->copy()->addWeeks($weeks)->toDateString();
    }
}
