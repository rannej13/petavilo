<?php

declare(strict_types=1);

namespace Modules\CatMarket\Support;

final class CatMarketSettings
{
    public static function companyLine(): string
    {
        if (! (bool) config('catmarket.show_company_details', false)) {
            return '';
        }

        $company = (array) config('catmarket.company', []);
        $parts = array_filter([
            (string) ($company['name'] ?? ''),
            filled($company['number'] ?? null) ? __('catmarket::messages.company_number', ['number' => $company['number']]) : '',
            (string) ($company['address'] ?? ''),
        ]);

        return implode(' · ', $parts);
    }

    public static function contactEmail(): string
    {
        return (string) config('catmarket.company.email', '');
    }

    public static function countryCodeFor(?string $countryName): string
    {
        $name = trim((string) $countryName);

        foreach ((array) config('catmarket.countries', []) as $code => $country) {
            if (strcasecmp($name, (string) $country['name']) === 0 || strcasecmp($name, (string) $code) === 0) {
                return (string) $code;
            }
        }

        return in_array(strtolower($name), ['uk', 'great britain', 'england', 'scotland', 'wales', 'northern ireland'], true) ? 'GB' : ($name === '' ? '' : 'OTHER');
    }

    public static function currencyForCountry(?string $countryName): string
    {
        $code = self::countryCodeFor($countryName);

        return (string) (config('catmarket.countries')[$code]['currency'] ?? 'GBP');
    }

    public static function visitorCountry(): string
    {
        $request = request();
        $chosen = strtoupper((string) $request->query('region', $request->cookie('petavilo_region', '')));

        if (array_key_exists($chosen, (array) config('catmarket.countries', []))) {
            return $chosen;
        }

        $country = strtoupper((string) ($request->header('CF-IPCountry') ?? $request->header('X-Country-Code') ?? ''));

        if (! preg_match('/^[A-Z]{2}$/', $country)) {
            $country = self::countryFromLanguage((string) $request->header('Accept-Language', ''));
        }

        return self::regionFor($country);
    }

    public static function regionFor(string $countryCode): string
    {
        return in_array(strtoupper($countryCode), (array) config('catmarket.americas', ['US']), true) ? 'US' : 'GB';
    }

    public static function visitorCountryId(): ?int
    {
        $name = (string) (config('catmarket.countries')[self::visitorCountry()]['name'] ?? '');

        if ($name === '') {
            return null;
        }

        $id = \Modules\Location\Models\Country::query()->where('name', $name)->value('id');

        return $id === null ? null : (int) $id;
    }

    private static function countryFromLanguage(string $header): string
    {
        foreach (explode(',', $header) as $part) {
            $tag = trim(explode(';', $part)[0]);

            if (preg_match('/^[a-z]{2,3}[-_]([A-Za-z]{2})$/', $tag, $match) === 1) {
                return strtoupper($match[1]);
            }
        }

        return '';
    }
}
