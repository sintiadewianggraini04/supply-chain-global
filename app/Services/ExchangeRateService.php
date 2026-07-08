<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExchangeRateService
{
    public function sync(string $baseCurrency = 'USD'): int
    {
        $baseCurrency = strtoupper($baseCurrency);

        $baseUrl = rtrim(
            (string) config('services.exchange_rate.base_url'),
            '/'
        );

        $response = Http::acceptJson()
            ->timeout(60)
            ->retry(3, 1000)
            ->get("{$baseUrl}/{$baseCurrency}");

        $response->throw();

        if ($response->json('result') !== 'success') {
            throw new RuntimeException(
                'ExchangeRate API gagal mengembalikan data kurs.'
            );
        }

        $rates = $response->json('rates', []);

        if (! is_array($rates) || $rates === []) {
            throw new RuntimeException(
                'Data rates kosong dari ExchangeRate API.'
            );
        }

        $targetCurrencies = [
            'IDR',
            'EUR',
            'CNY',
            'JPY',
            'AUD',
            'GBP',
            'SGD',
            'MYR',
            'KRW',
            'THB',
        ];

        $rateDate = Carbon::createFromTimestamp(
            (int) $response->json('time_last_update_unix')
        )->toDateString();

        $fetchedAt = now();

        $savedRates = 0;

        foreach ($targetCurrencies as $targetCurrency) {
            if (! array_key_exists($targetCurrency, $rates)) {
                continue;
            }

            ExchangeRate::updateOrCreate(
                [
                    'base_currency' => $baseCurrency,
                    'target_currency' => $targetCurrency,
                    'rate_date' => $rateDate,
                ],
                [
                    'rate' => $rates[$targetCurrency],
                    'fetched_at' => $fetchedAt,
                    'provider' => 'ExchangeRate-API',
                    'raw_response' => [
                        'result' => $response->json('result'),
                        'time_last_update_utc' => $response->json(
                            'time_last_update_utc'
                        ),
                    ],
                ]
            );

            $savedRates++;
        }

        return $savedRates;
    }
}