<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class WorldBankService
{
    private const BASE_URL =
        'https://api.worldbank.org/v2';

    private const GDP_INDICATOR =
        'NY.GDP.MKTP.CD';

    private const INFLATION_INDICATOR =
        'FP.CPI.TOTL.ZG';

    /**
     * Mengambil GDP dan inflasi terbaru
     * untuk satu negara.
     */
    public function getCountryIndicators(
        string $countryCode
    ): array {
        $dataset =
            $this->getCountryDashboardData(
                $countryCode,
                10
            );

        return [
            'gdp' =>
                $dataset['gdp'] ?? null,

            'inflation' =>
                $dataset['inflation'] ?? null,
        ];
    }

    /**
     * Mengambil GDP dan inflasi beberapa negara.
     * Digunakan oleh Country Comparison.
     */
    public function getCountriesIndicators(
        array $countryCodes
    ): array {
        $countryCodes =
            $this->normalizeCountryCodes(
                $countryCodes
            );

        if ($countryCodes === []) {
            throw new RuntimeException(
                'Kode negara World Bank tidak valid.'
            );
        }

        $results = [];

        foreach ($countryCodes as $countryCode) {
            $dataset =
                $this->getCountryDashboardData(
                    $countryCode,
                    10
                );

            $results[$countryCode] = [
                'gdp' =>
                    $dataset['gdp'] ?? null,

                'inflation' =>
                    $dataset['inflation'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * Mengambil data terbaru dan data tren
     * GDP serta inflasi dari World Bank API.
     */
    public function getCountryDashboardData(
        string $countryCode,
        int $years = 10
    ): array {
        $countryCode =
            $this->normalizeCountryCode(
                $countryCode
            );

        $years = max(
            5,
            min(30, $years)
        );

        $currentYear = now()->year;

        /*
         * Ditambah empat tahun karena beberapa
         * indikator World Bank memiliki jeda
         * publikasi.
         */
        $startYear =
            $currentYear - ($years + 4);

        $errors = [];

        $gdpTrend = [];

        $inflationTrend = [];

        try {
            $gdpTrend =
                $this->getIndicatorSeries(
                    countryCode:
                        $countryCode,

                    indicatorCode:
                        self::GDP_INDICATOR,

                    startYear:
                        $startYear,

                    endYear:
                        $currentYear,

                    maximumRecords:
                        $years
                );
        } catch (Throwable $exception) {
            report($exception);

            $errors[] =
                $this->formatError(
                    'Data GDP dari World Bank tidak tersedia.',
                    $exception
                );
        }

        try {
            $inflationTrend =
                $this->getIndicatorSeries(
                    countryCode:
                        $countryCode,

                    indicatorCode:
                        self::INFLATION_INDICATOR,

                    startYear:
                        $startYear,

                    endYear:
                        $currentYear,

                    maximumRecords:
                        $years
                );
        } catch (Throwable $exception) {
            report($exception);

            $errors[] =
                $this->formatError(
                    'Data inflasi dari World Bank tidak tersedia.',
                    $exception
                );
        }

        return [
            'gdp' =>
                $this->latestValue(
                    $gdpTrend
                ),

            'inflation' =>
                $this->latestValue(
                    $inflationTrend
                ),

            'trends' => [
                'gdp' =>
                    $gdpTrend,

                'inflation' =>
                    $inflationTrend,
            ],

            'errors' =>
                array_values(
                    array_unique(
                        array_filter($errors)
                    )
                ),
        ];
    }

    /**
     * Mengambil satu indikator World Bank.
     *
     * Hanya satu request, tanpa variasi URL
     * dan tanpa retry berulang.
     */
    private function getIndicatorSeries(
        string $countryCode,
        string $indicatorCode,
        int $startYear,
        int $endYear,
        int $maximumRecords
    ): array {
        $cacheKey = implode(
            ':',
            [
                'world-bank',
                strtolower($countryCode),
                strtolower($indicatorCode),
                $startYear,
                $endYear,
            ]
        );

        /*
         * Cache hanya berisi data yang pernah
         * berhasil diterima dari World Bank API.
         */
        $cached = Cache::get(
            $cacheKey
        );

        if (
            is_array($cached)
            && $cached !== []
        ) {
            return $cached;
        }

        $url =
            self::BASE_URL
            . "/country/{$countryCode}"
            . "/indicator/{$indicatorCode}";

        $response =
            Http::withHeaders([
                'Accept' =>
                    'application/json',

                'User-Agent' =>
                    config(
                        'app.name',
                        'Supply Chain Global'
                    )
                    . '/1.0',
            ])
                ->connectTimeout(5)
                ->timeout(10)
                ->get(
                    $url,
                    [
                        'format' =>
                            'json',

                        'source' =>
                            2,

                        'date' =>
                            "{$startYear}:{$endYear}",

                        'per_page' =>
                            100,
                    ]
                );

        if (! $response->successful()) {
            throw new RuntimeException(
                "World Bank API menghasilkan HTTP {$response->status()}."
            );
        }

        $payload =
            $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException(
                'Respons World Bank bukan JSON yang valid.'
            );
        }

        $apiError =
            $this->extractApiError(
                $payload
            );

        if ($apiError !== null) {
            throw new RuntimeException(
                $apiError
            );
        }

        $series =
            $this->parseSeries(
                payload:
                    $payload,

                countryCode:
                    $countryCode,

                indicatorCode:
                    $indicatorCode
            );

        if ($series === []) {
            throw new RuntimeException(
                "World Bank tidak mengembalikan nilai {$indicatorCode} untuk {$countryCode}."
            );
        }

        /*
         * Ambil maksimal sejumlah tahun yang
         * dibutuhkan dashboard.
         */
        if (
            count($series)
            > $maximumRecords
        ) {
            $series =
                array_slice(
                    $series,
                    -$maximumRecords
                );
        }

        /*
         * Cache hanya disimpan jika World Bank
         * memberikan data yang valid.
         */
        Cache::put(
            $cacheKey,
            $series,
            now()->addHours(6)
        );

        return $series;
    }

    private function parseSeries(
        array $payload,
        string $countryCode,
        string $indicatorCode
    ): array {
        $records =
            $payload[1] ?? null;

        if (! is_array($records)) {
            return [];
        }

        $series = [];

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            $value =
                $record['value'] ?? null;

            $year =
                $record['date'] ?? null;

            /*
             * Tahun dengan nilai null tidak
             * ditampilkan dalam grafik.
             */
            if (
                ! is_numeric($value)
                || ! is_numeric($year)
            ) {
                continue;
            }

            $recordCountryCode =
                strtoupper(
                    trim(
                        (string) (
                            $record[
                                'countryiso3code'
                            ]
                            ?? ''
                        )
                    )
                );

            if (
                $recordCountryCode !== ''
                && $recordCountryCode
                    !== $countryCode
            ) {
                continue;
            }

            $series[] = [
                'indicator' =>
                    $indicatorCode,

                'name' =>
                    data_get(
                        $record,
                        'indicator.value'
                    ),

                'country_code' =>
                    $countryCode,

                'country_name' =>
                    data_get(
                        $record,
                        'country.value'
                    ),

                'year' =>
                    (int) $year,

                'value' =>
                    (float) $value,
            ];
        }

        /*
         * World Bank biasanya mengurutkan data
         * dari tahun terbaru. Chart membutuhkan
         * tahun lama ke tahun terbaru.
         */
        usort(
            $series,
            fn (
                array $first,
                array $second
            ): int =>
                $first['year']
                <=> $second['year']
        );

        /*
         * Menghapus kemungkinan data tahun
         * yang duplikat.
         */
        $uniqueSeries = [];

        foreach ($series as $item) {
            $uniqueSeries[
                $item['year']
            ] = $item;
        }

        ksort(
            $uniqueSeries
        );

        return array_values(
            $uniqueSeries
        );
    }

    private function latestValue(
        array $series
    ): ?array {
        if ($series === []) {
            return null;
        }

        $lastKey =
            array_key_last(
                $series
            );

        if ($lastKey === null) {
            return null;
        }

        return $series[$lastKey];
    }

    private function normalizeCountryCodes(
        array $countryCodes
    ): array {
        $normalized = [];

        foreach ($countryCodes as $countryCode) {
            try {
                $normalized[] =
                    $this->normalizeCountryCode(
                        (string) $countryCode
                    );
            } catch (RuntimeException) {
                continue;
            }
        }

        $normalized =
            array_values(
                array_unique(
                    $normalized
                )
            );

        sort(
            $normalized
        );

        return $normalized;
    }

    private function normalizeCountryCode(
        string $countryCode
    ): string {
        $countryCode =
            strtoupper(
                trim($countryCode)
            );

        if (
            preg_match(
                '/^[A-Z]{3}$/',
                $countryCode
            ) !== 1
        ) {
            throw new RuntimeException(
                'Kode negara World Bank harus menggunakan kode ISO3.'
            );
        }

        return $countryCode;
    }

    private function extractApiError(
        array $payload
    ): ?string {
        $message =
            data_get(
                $payload,
                '0.message.0.value'
            );

        if (
            is_string($message)
            && trim($message) !== ''
        ) {
            return trim($message);
        }

        $message =
            data_get(
                $payload,
                'message.0.value'
            );

        if (
            is_string($message)
            && trim($message) !== ''
        ) {
            return trim($message);
        }

        return null;
    }

   private function formatError(
    string $message,
    Throwable $exception
): string {
    return $message;
}
}