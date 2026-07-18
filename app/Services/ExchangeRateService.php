<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExchangeRateService
{
    /**
     * Kompatibel dengan command:
     *
     * php artisan currency:sync USD
     */
    public function sync(
    string $base = 'USD',
    bool $forceRefresh = true
): int {
    $result = $this->latest(
        $base,
        $forceRefresh
    );

    return (int) (
        $result['processed_count']
        ?? 0
    );
}

    /**
     * Mengambil nilai kurs terbaru dari
     * ExchangeRate-API Open Access.
     */
    public function latest(
        string $base = 'USD',
        bool $forceRefresh = false
    ): array {
        $base = $this->normalizeCode($base);

        $cacheKey = sprintf(
            'open-exchange-rate:latest:%s',
            strtolower($base)
        );

        if ($forceRefresh) {
            Cache::forget($cacheKey);

            return $this->requestAndStore(
                $base
            );
        }

        $cacheMinutes = max(
            1,
            (int) config(
                'services.exchange_rate.cache_minutes',
                60
            )
        );

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(
                $cacheMinutes
            ),
            fn (): array =>
                $this->requestAndStore($base)
        );
    }

    /**
     * Mengambil riwayat pasangan mata uang
     * berdasarkan snapshot yang tersimpan
     * di database lokal.
     */
    public function pairHistory(
        string $base,
        string $target,
        int $days = 30
    ): Collection {
        $base = $this->normalizeCode(
            $base
        );

        $target = $this->normalizeCode(
            $target
        );

        $days = max(
            1,
            min($days, 365)
        );

        if ($base === $target) {
            return collect([
                [
                    'date' =>
                        now()->toDateString(),

                    'rate' => 1.0,

                    'provider' =>
                        'Internal Conversion',

                    'fetched_at' =>
                        now()->toISOString(),
                ],
            ]);
        }

        $startDate = now()
            ->subDays($days - 1)
            ->startOfDay()
            ->toDateString();

        /*
         * Cari direct pair terlebih dahulu.
         */
        $directHistory =
            ExchangeRate::query()
                ->where(
                    'base_currency',
                    $base
                )
                ->where(
                    'target_currency',
                    $target
                )
                ->whereDate(
                    'rate_date',
                    '>=',
                    $startDate
                )
                ->orderBy('rate_date')
                ->orderBy('fetched_at')
                ->get()
                ->groupBy(
                    fn (ExchangeRate $row):
                        string =>
                            $row->rate_date
                                ->toDateString()
                )
                ->map(
                    function (
                        Collection $rows,
                        string $date
                    ): array {
                        /** @var ExchangeRate $row */
                        $row = $rows->last();

                        return [
                            'date' => $date,

                            'rate' =>
                                (float) $row->rate,

                            'provider' =>
                                $row->provider
                                ?? 'ExchangeRate-API',

                            'fetched_at' =>
                                $row->fetched_at
                                    ?->toISOString(),
                        ];
                    }
                )
                ->sortKeys()
                ->values();

        if ($directHistory->isNotEmpty()) {
            return $directHistory;
        }

        /*
         * Pasangan non-USD dihitung melalui
         * cross-rate:
         *
         * BASE/TARGET =
         * USD/TARGET dibagi USD/BASE.
         */
        $requiredCurrencies = collect([
            $base,
            $target,
        ])
            ->reject(
                fn (string $currency):
                    bool =>
                        $currency === 'USD'
            )
            ->unique()
            ->values();

        $usdRows = ExchangeRate::query()
            ->where(
                'base_currency',
                'USD'
            )
            ->whereIn(
                'target_currency',
                $requiredCurrencies->all()
            )
            ->whereDate(
                'rate_date',
                '>=',
                $startDate
            )
            ->orderBy('rate_date')
            ->orderBy('fetched_at')
            ->get();

        return $usdRows
            ->groupBy(
                fn (ExchangeRate $row):
                    string =>
                        $row->rate_date
                            ->toDateString()
            )
            ->map(
                function (
                    Collection $rows,
                    string $date
                ) use (
                    $base,
                    $target
                ): ?array {
                    $baseRow =
                        $base === 'USD'
                            ? null
                            : $rows->firstWhere(
                                'target_currency',
                                $base
                            );

                    $targetRow =
                        $target === 'USD'
                            ? null
                            : $rows->firstWhere(
                                'target_currency',
                                $target
                            );

                    $baseRate =
                        $base === 'USD'
                            ? 1.0
                            : (float) (
                                $baseRow?->rate
                                ?? 0
                            );

                    $targetRate =
                        $target === 'USD'
                            ? 1.0
                            : (float) (
                                $targetRow?->rate
                                ?? 0
                            );

                    if (
                        $baseRate <= 0
                        || $targetRate <= 0
                    ) {
                        return null;
                    }

                    $reference =
                        $targetRow
                        ?? $baseRow
                        ?? $rows->first();

                    return [
                        'date' => $date,

                        'rate' => round(
                            $targetRate
                            / $baseRate,
                            10
                        ),

                        'provider' =>
                            $reference?->provider
                            ?? 'ExchangeRate-API',

                        'fetched_at' =>
                            $reference?->fetched_at
                                ?->toISOString(),
                    ];
                }
            )
            ->filter()
            ->sortKeys()
            ->values();
    }

    /**
     * Daftar mata uang yang tersedia
     * di database.
     */
    public function currencyCodes():
        Collection
    {
        $baseCurrencies =
            ExchangeRate::query()
                ->select('base_currency')
                ->distinct()
                ->pluck('base_currency');

        $targetCurrencies =
            ExchangeRate::query()
                ->select('target_currency')
                ->distinct()
                ->pluck('target_currency');

        $currencies = $baseCurrencies
            ->merge($targetCurrencies)
            ->map(
                fn ($currency): string =>
                    strtoupper(
                        trim(
                            (string) $currency
                        )
                    )
            )
            ->filter(
                fn (string $currency):
                    bool =>
                        preg_match(
                            '/^[A-Z]{3}$/',
                            $currency
                        ) === 1
            )
            ->unique()
            ->sort()
            ->values();

        if ($currencies->isEmpty()) {
            return collect([
                'AUD',
                'CAD',
                'CHF',
                'CNY',
                'EUR',
                'GBP',
                'IDR',
                'INR',
                'JPY',
                'KRW',
                'MYR',
                'NZD',
                'PHP',
                'SAR',
                'SGD',
                'THB',
                'USD',
            ]);
        }

        if (! $currencies->contains('USD')) {
            $currencies->push('USD');
        }

        if (! $currencies->contains('IDR')) {
            $currencies->push('IDR');
        }

        return $currencies
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Meminta data langsung ke API
     * tanpa API key.
     */
    private function requestAndStore(
        string $base
    ): array {
        $baseUrl = rtrim(
            (string) config(
                'services.exchange_rate.base_url',
                'https://open.er-api.com/v6'
            ),
            '/'
        );

        $timeout = max(
            5,
            (int) config(
                'services.exchange_rate.timeout',
                12
            )
        );

        $url = sprintf(
            '%s/latest/%s',
            $baseUrl,
            $base
        );

        $response = Http::acceptJson()
            ->connectTimeout(4)
            ->timeout($timeout)
            ->retry(
                2,
                400,
                throw: false
            )
            ->get($url);

        if (
            $response->status() === 429
        ) {
            throw new RuntimeException(
                'ExchangeRate-API sedang membatasi '
                . 'jumlah request. Coba kembali nanti.'
            );
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                sprintf(
                    'ExchangeRate-API gagal diakses. HTTP %d.',
                    $response->status()
                )
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException(
                'Respons ExchangeRate-API '
                . 'tidak valid.'
            );
        }

        if (
            ($payload['result'] ?? null)
            !== 'success'
        ) {
            $errorType =
                $payload['error-type']
                ?? 'unknown-error';

            throw new RuntimeException(
                "ExchangeRate-API error: "
                . "{$errorType}."
            );
        }

        /*
         * Open Access menggunakan field "rates",
         * bukan "conversion_rates".
         */
        $rates = $payload['rates']
            ?? null;

        if (
            ! is_array($rates)
            || $rates === []
        ) {
            throw new RuntimeException(
                'Data rates tidak ditemukan '
                . 'dalam respons API.'
            );
        }

        $providerUpdatedUnix = (int) (
            $payload[
                'time_last_update_unix'
            ]
            ?? 0
        );

        $providerNextUpdateUnix = (int) (
            $payload[
                'time_next_update_unix'
            ]
            ?? 0
        );

        $providerUpdatedAt =
            $providerUpdatedUnix > 0
                ? CarbonImmutable
                    ::createFromTimestampUTC(
                        $providerUpdatedUnix
                    )
                : CarbonImmutable::now(
                    'UTC'
                );

        $providerNextUpdateAt =
            $providerNextUpdateUnix > 0
                ? CarbonImmutable
                    ::createFromTimestampUTC(
                        $providerNextUpdateUnix
                    )
                : null;

        $rateDate =
            $providerUpdatedAt
                ->toDateString();

        $fetchedAt = now();

        $processedCount = 0;

        DB::transaction(
            function () use (
                $rates,
                $base,
                $rateDate,
                $fetchedAt,
                $payload,
                &$processedCount
            ): void {
                foreach (
                    $rates as
                    $currency => $rate
                ) {
                    $currency = strtoupper(
                        trim(
                            (string) $currency
                        )
                    );

                    if (
                        preg_match(
                            '/^[A-Z]{3}$/',
                            $currency
                        ) !== 1
                        || ! is_numeric($rate)
                    ) {
                        continue;
                    }

                    ExchangeRate::query()
                        ->updateOrCreate(
                            [
                                'base_currency' =>
                                    $base,

                                'target_currency' =>
                                    $currency,

                                'rate_date' =>
                                    $rateDate,
                            ],
                            [
                                'rate' =>
                                    (float) $rate,

                                'fetched_at' =>
                                    $fetchedAt,

                                'provider' =>
                                    'ExchangeRate-API Open Access',

                                'raw_response' =>
                                    $payload,
                            ]
                        );

                    $processedCount++;
                }
            }
        );

        return [
            'base_currency' =>
                $payload['base_code']
                ?? $base,

            'rates' => collect($rates)
                ->mapWithKeys(
                    fn (
                        $rate,
                        $currency
                    ): array => [
                        strtoupper(
                            (string) $currency
                        ) => (float) $rate,
                    ]
                )
                ->all(),

            'provider' =>
                'ExchangeRate-API Open Access',

            'provider_updated_at' =>
                $providerUpdatedAt
                    ->toISOString(),

            'provider_next_update_at' =>
                $providerNextUpdateAt
                    ?->toISOString(),

            'rate_date' =>
                $rateDate,

            'fetched_at' =>
                $fetchedAt
                    ->toISOString(),

            'processed_count' =>
                $processedCount,
        ];
    }

    private function normalizeCode(
        string $currency
    ): string {
        $currency = strtoupper(
            trim($currency)
        );

        if (
            preg_match(
                '/^[A-Z]{3}$/',
                $currency
            ) !== 1
        ) {
            throw new RuntimeException(
                "Kode mata uang "
                . "{$currency} tidak valid."
            );
        }

        return $currency;
    }
}