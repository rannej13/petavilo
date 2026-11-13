<?php

declare(strict_types=1);

namespace Modules\PetTools\Support;

final class InsuranceSimulator
{
    public static function defaults(string $country): array
    {
        return $country === 'US'
            ? ['saving' => 40, 'premium' => 30, 'increase' => 10, 'bill' => 3200, 'year' => 2, 'excess' => 250, 'limit' => 10000]
            : ['saving' => 30, 'premium' => 20, 'increase' => 10, 'bill' => 3000, 'year' => 2, 'excess' => 150, 'limit' => 7000];
    }

    public static function run(array $input): array
    {
        $saving = max(0.0, (float) $input['saving']);
        $premium = max(0.0, (float) $input['premium']);
        $increase = max(0.0, min(50.0, (float) $input['increase'])) / 100;
        $bill = max(0.0, (float) $input['bill']);
        $billYear = max(0, min(5, (int) $input['year']));
        $excess = max(0.0, (float) $input['excess']);
        $limit = max(0.0, (float) $input['limit']);

        $rows = [];
        $saved = 0.0;
        $premiums = 0.0;
        $insuredExtra = 0.0;

        for ($year = 1; $year <= 5; $year++) {
            $saved += $saving * 12;
            $premiums += $premium * 12 * ((1 + $increase) ** ($year - 1));

            if ($year === $billYear) {
                $saved -= $bill;
                $insuredExtra += $excess + max(0.0, $bill - $excess - $limit);
            }

            $rows[] = [
                'year' => $year,
                'savings_balance' => round($saved, 2),
                'insured_cost' => round($premiums + $insuredExtra, 2),
                'savings_cost' => round($saving * 12 * $year, 2),
            ];
        }

        $final = end($rows);

        return [
            'rows' => $rows,
            'shortfall' => $billYear > 0 && ($saving * 12 * $billYear) < $bill ? round($bill - $saving * 12 * $billYear, 2) : 0.0,
            'savings_left' => $final['savings_balance'],
            'insured_total' => $final['insured_cost'],
            'max' => max(1.0, max(array_map(static fn (array $row): float => max(abs($row['savings_balance']), $row['insured_cost']), $rows))),
        ];
    }
}
