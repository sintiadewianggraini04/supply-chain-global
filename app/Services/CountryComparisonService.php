<?php

namespace App\Services;

use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CountryComparisonService
{
    public function __construct(
        private readonly WorldBankService $worldBankService,
        private readonly OpenMeteoService $openMeteoService,
        private readonly RiskScoringService $riskScoringService
    ) {
    }

    public function compare(
        Country $countryA,
        Country $countryB
    ): array {
        $countryACode = strtoupper(
            (string) $countryA->cca3
        );

        $countryBCode = strtoupper(
            (string) $countryB->cca3
        );

        $economyResults = [];
        $economyError = null;

        try {
            $economyResults =
                $this->worldBankService
                    ->getCountriesIndicators([
                        $countryACode,
                        $countryBCode,
                    ]);
        } catch (Throwable $exception) {
            report($exception);

            $economyError =
                'Data World Bank tidak tersedia.';
        }

        $weatherResults = [];
        $weatherError = null;
        $weatherLocations = [];

        foreach (
            [$countryA, $countryB] as $country
        ) {
            if (
                ! is_numeric($country->latitude)
                || ! is_numeric($country->longitude)
            ) {
                continue;
            }

            $weatherLocations[] = [
                'key' => strtoupper(
                    (string) $country->cca3
                ),

                'latitude' =>
                    (float) $country->latitude,

                'longitude' =>
                    (float) $country->longitude,
            ];
        }

        if ($weatherLocations !== []) {
            try {
                $weatherKeys = collect(
                    $weatherLocations
                )
                    ->pluck('key')
                    ->sort()
                    ->implode(':');

                $weatherResults = Cache::remember(
                    'comparison-weather-batch:'
                        . $weatherKeys,
                    now()->addMinutes(30),
                    fn () => $this->openMeteoService
                        ->getForecasts(
                            $weatherLocations
                        )
                );
            } catch (Throwable $exception) {
                report($exception);

                $weatherError =
                    'Data cuaca tidak tersedia.';
            }
        }

        $firstCountry =
            $this->buildCountrySnapshot(
                $countryA,

                $economyResults[
                    $countryACode
                ] ?? null,

                $economyError,

                $weatherResults[
                    $countryACode
                ] ?? null,

                $weatherError
            );

        $secondCountry =
            $this->buildCountrySnapshot(
                $countryB,

                $economyResults[
                    $countryBCode
                ] ?? null,

                $economyError,

                $weatherResults[
                    $countryBCode
                ] ?? null,

                $weatherError
            );

        return [
            'country_a' => $firstCountry,

            'country_b' => $secondCountry,

            'recommendation' =>
                $this->buildRecommendation(
                    $firstCountry,
                    $secondCountry
                ),

            'methodology' =>
                $firstCountry['risk']['methodology']
                ?? null,
        ];
    }

    private function buildCountrySnapshot(
        Country $country,
        ?array $economy,
        ?string $economyError,
        ?array $weather,
        ?string $weatherError
    ): array {
        $errors = [];

        $economy ??= [
            'gdp' => null,
            'inflation' => null,
        ];

        if ($economyError !== null) {
            $errors[] = $economyError;
        }

        if ($weatherError !== null) {
            $errors[] = $weatherError;
        } elseif (
            ! is_numeric($country->latitude)
            || ! is_numeric($country->longitude)
        ) {
            $errors[] =
                'Koordinat negara tidak tersedia.';
        } elseif ($weather === null) {
            $errors[] =
                'Data cuaca tidak tersedia.';
        }

        $currency =
            $this->getCurrencySnapshot(
                $country->currency_code
            );

        if ($currency === null) {
            $errors[] =
                'Data kurs mata uang tidak tersedia.';
        }

        /*
         * Country Comparison tidak lagi
         * menghitung risiko sendiri.
         *
         * Skor diambil dari Risk Scoring Engine.
         */
        $risk = $this->riskScoringService
            ->scoreCountry(
                $country,
                $economy,
                $weather
            );

        return [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,

                'official_name' =>
                    $country->official_name,

                'cca2' => $country->cca2,
                'cca3' => $country->cca3,

                'capital' => $country->capital,
                'region' => $country->region,

                'population' =>
                    $country->population,

                'latitude' =>
                    $country->latitude !== null
                        ? (float) $country->latitude
                        : null,

                'longitude' =>
                    $country->longitude !== null
                        ? (float) $country->longitude
                        : null,

                'flag_url' =>
                    $country->flag_url,

                'currency_code' =>
                    $country->currency_code,

                'currency_name' =>
                    $country->currency_name,

                'currency_symbol' =>
                    $country->currency_symbol,
            ],

            'economy' => $economy,
            'weather' => $weather,
            'currency' => $currency,
            'risk' => $risk,

            'errors' => array_values(
                array_unique(
                    array_merge(
                        $errors,
                        $risk['errors'] ?? []
                    )
                )
            ),
        ];
    }

    private function getCurrencySnapshot(
        ?string $currencyCode
    ): ?array {
        if ($currencyCode === null) {
            return null;
        }

        $currencyCode = strtoupper(
            trim($currencyCode)
        );

        if (
            preg_match(
                '/^[A-Z]{3}$/',
                $currencyCode
            ) !== 1
        ) {
            return null;
        }

        $latestRateDate =
            ExchangeRate::query()
                ->where(
                    'base_currency',
                    'USD'
                )
                ->max('rate_date');

        if ($latestRateDate === null) {
            return null;
        }

        if ($currencyCode === 'USD') {
            return [
                'base_currency' => 'USD',
                'target_currency' => 'USD',
                'rate' => 1.0,
                'rate_date' => $latestRateDate,
                'provider' => 'ExchangeRate-API',
                'fetched_at' => null,
            ];
        }

        $rate = ExchangeRate::query()
            ->where(
                'base_currency',
                'USD'
            )
            ->where(
                'target_currency',
                $currencyCode
            )
            ->whereDate(
                'rate_date',
                $latestRateDate
            )
            ->latest('fetched_at')
            ->first();

        if ($rate === null) {
            return null;
        }

        return [
            'base_currency' =>
                $rate->base_currency,

            'target_currency' =>
                $rate->target_currency,

            'rate' =>
                (float) $rate->rate,

            'rate_date' =>
                $rate->rate_date
                    ?->toDateString(),

            'provider' =>
                $rate->provider,

            'fetched_at' =>
                $rate->fetched_at
                    ?->toISOString(),
        ];
    }

    private function buildRecommendation(
        array $countryA,
        array $countryB
    ): array {
        $scoreA = data_get(
            $countryA,
            'risk.score'
        );

        $scoreB = data_get(
            $countryB,
            'risk.score'
        );

        $nameA = data_get(
            $countryA,
            'country.name',
            'Country A'
        );

        $nameB = data_get(
            $countryB,
            'country.name',
            'Country B'
        );

        if (
            ! is_numeric($scoreA)
            || ! is_numeric($scoreB)
        ) {
            return [
                'preferred_country' => null,

                'message' =>
                    'Data belum cukup untuk menentukan '
                    . 'negara dengan estimasi risiko '
                    . 'lebih rendah.',
            ];
        }

        $difference = abs(
            (float) $scoreA
            - (float) $scoreB
        );

        if ($difference < 5) {
            return [
                'preferred_country' => null,

                'message' =>
                    "{$nameA} dan {$nameB} memiliki "
                    . 'estimasi tingkat risiko '
                    . 'yang relatif sama.',
            ];
        }

        if ($scoreA < $scoreB) {
            return [
                'preferred_country' =>
                    $nameA,

                'message' =>
                    "{$nameA} memiliki estimasi risiko "
                    . "lebih rendah dibandingkan {$nameB}.",
            ];
        }

        return [
            'preferred_country' =>
                $nameB,

            'message' =>
                "{$nameB} memiliki estimasi risiko "
                . "lebih rendah dibandingkan {$nameA}.",
        ];
    }
}