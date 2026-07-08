<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RestCountriesService
{
    public function sync(): int
{
    $baseUrl = rtrim(
        (string) config('services.rest_countries.base_url'),
        '/'
    );

    $apiKey = (string) config('services.rest_countries.api_key');

    if ($apiKey === '') {
        throw new RuntimeException(
            'REST_COUNTRIES_API_KEY belum diisi di file .env.'
        );
    }

    $limit = 100;
    $offset = 0;
    $savedCountries = 0;

    do {
        $response = Http::acceptJson()
            ->withToken($apiKey)
            ->timeout(60)
            ->retry(3, 1000)
            ->get($baseUrl, [
                'limit' => $limit,
                'offset' => $offset,

                'response_fields' => implode(',', [
                    'names.common',
                    'names.official',
                    'codes.alpha_2',
                    'codes.alpha_3',
                    'capitals',
                    'region',
                    'subregion',
                    'currencies',
                    'languages',
                    'population',
                    'coordinates.lat',
                    'coordinates.lng',
                    'flag.url_svg',
                ]),
            ]);

        $response->throw();

        $countries = $response->json('data.objects', []);

        if ($countries === []) {
            $countries = $response->json('data', []);
        }

        if ($countries === []) {
            break;
        }

        foreach ($countries as $countryData) {
            $cca3 = strtoupper(
                (string) data_get($countryData, 'codes.alpha_3')
            );

            if ($cca3 === '') {
                continue;
            }

            [
                $currencyCode,
                $currencyName,
                $currencySymbol,
            ] = $this->extractCurrency($countryData);

            Country::updateOrCreate(
                [
                    'cca3' => $cca3,
                ],
                [
                    'name' => data_get(
                        $countryData,
                        'names.common',
                        $cca3
                    ),

                    'official_name' => data_get(
                        $countryData,
                        'names.official'
                    ),

                    'cca2' => strtoupper(
                        (string) data_get(
                            $countryData,
                            'codes.alpha_2'
                        )
                    ) ?: null,

                    'capital' => data_get(
                        $countryData,
                        'capitals.0.name'
                    ),

                    'region' => data_get(
                        $countryData,
                        'region'
                    ),

                    'subregion' => data_get(
                        $countryData,
                        'subregion'
                    ),

                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'currency_symbol' => $currencySymbol,

                    'languages' => data_get(
                        $countryData,
                        'languages',
                        []
                    ),

                    'population' => data_get(
                        $countryData,
                        'population'
                    ),

                    'latitude' => data_get(
                        $countryData,
                        'coordinates.lat'
                    ),

                    'longitude' => data_get(
                        $countryData,
                        'coordinates.lng'
                    ),

                    'flag_url' => data_get(
                        $countryData,
                        'flag.url_svg'
                    ),

                    'is_active' => true,
                ]
            );

            $savedCountries++;
        }

        $count = count($countries);
        $offset += $limit;

        $hasMoreData =
            $response->json('data.meta.more') === true
            || $count === $limit;
    } while ($hasMoreData);

    return $savedCountries;
}

    private function extractCurrency(array $countryData): array
    {
        $currencies = data_get($countryData, 'currencies', []);

        if (! is_array($currencies) || $currencies === []) {
            return [null, null, null];
        }

        if (array_is_list($currencies)) {
            $currency = $currencies[0] ?? [];

            if (! is_array($currency)) {
                return [null, null, null];
            }

            return [
                $currency['code'] ?? $currency['iso_4217'] ?? null,
                $currency['name'] ?? null,
                $currency['symbol'] ?? null,
            ];
        }

        $currencyCode = array_key_first($currencies);
        $currency = $currencies[$currencyCode] ?? [];

        if (! is_array($currency)) {
            return [$currencyCode, null, null];
        }

        return [
            $currencyCode,
            $currency['name'] ?? null,
            $currency['symbol'] ?? null,
        ];
    }
}