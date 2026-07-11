<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenMeteoService
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';

    public function getForecast(
        float $latitude,
        float $longitude
    ): array {
        $response = Http::acceptJson()
            ->timeout(20)
            ->retry(2, 500)
            ->get(self::API_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,

                'current' => implode(',', [
                    'temperature_2m',
                    'relative_humidity_2m',
                    'apparent_temperature',
                    'precipitation',
                    'rain',
                    'weather_code',
                    'cloud_cover',
                    'wind_speed_10m',
                    'wind_gusts_10m',
                ]),

                'daily' => implode(',', [
                    'weather_code',
                    'temperature_2m_max',
                    'temperature_2m_min',
                    'precipitation_sum',
                    'precipitation_probability_max',
                    'wind_speed_10m_max',
                    'wind_gusts_10m_max',
                ]),

                'temperature_unit' => 'celsius',
                'wind_speed_unit' => 'kmh',
                'precipitation_unit' => 'mm',
                'timezone' => 'auto',
                'forecast_days' => 7,
            ]);

        $response->throw();

        $data = $response->json();

        if (
            ! is_array($data)
            || ! isset($data['current'])
            || ! isset($data['daily'])
        ) {
            throw new RuntimeException(
                'Format respons Open-Meteo tidak sesuai.'
            );
        }

        return $this->formatResponse($data);
    }

    private function formatResponse(array $data): array
    {
        $currentWeatherCode = $this->toInteger(
            data_get($data, 'current.weather_code')
        );

        $current = [
            'time' => data_get($data, 'current.time'),

            'temperature' => $this->toNumber(
                data_get($data, 'current.temperature_2m')
            ),

            'apparent_temperature' => $this->toNumber(
                data_get($data, 'current.apparent_temperature')
            ),

            'humidity' => $this->toNumber(
                data_get(
                    $data,
                    'current.relative_humidity_2m'
                )
            ),

            'precipitation' => $this->toNumber(
                data_get($data, 'current.precipitation')
            ),

            'rain' => $this->toNumber(
                data_get($data, 'current.rain')
            ),

            'cloud_cover' => $this->toNumber(
                data_get($data, 'current.cloud_cover')
            ),

            'wind_speed' => $this->toNumber(
                data_get($data, 'current.wind_speed_10m')
            ),

            'wind_gusts' => $this->toNumber(
                data_get($data, 'current.wind_gusts_10m')
            ),

            'weather_code' => $currentWeatherCode,

            'description' => $this->weatherDescription(
                $currentWeatherCode
            ),
        ];

        $forecast = [];

        $dates = data_get($data, 'daily.time', []);

        foreach ($dates as $index => $date) {
            $weatherCode = $this->toInteger(
                data_get(
                    $data,
                    "daily.weather_code.$index"
                )
            );

            $forecast[] = [
                'date' => $date,

                'weather_code' => $weatherCode,

                'description' => $this->weatherDescription(
                    $weatherCode
                ),

                'temperature_max' => $this->toNumber(
                    data_get(
                        $data,
                        "daily.temperature_2m_max.$index"
                    )
                ),

                'temperature_min' => $this->toNumber(
                    data_get(
                        $data,
                        "daily.temperature_2m_min.$index"
                    )
                ),

                'precipitation_sum' => $this->toNumber(
                    data_get(
                        $data,
                        "daily.precipitation_sum.$index"
                    )
                ),

                'precipitation_probability' => $this->toNumber(
                    data_get(
                        $data,
                        "daily.precipitation_probability_max.$index"
                    )
                ),

                'wind_speed_max' => $this->toNumber(
                    data_get(
                        $data,
                        "daily.wind_speed_10m_max.$index"
                    )
                ),

                'wind_gusts_max' => $this->toNumber(
                    data_get(
                        $data,
                        "daily.wind_gusts_10m_max.$index"
                    )
                ),
            ];
        }

        return [
            'timezone' => data_get(
                $data,
                'timezone',
                'UTC'
            ),

            'timezone_abbreviation' => data_get(
                $data,
                'timezone_abbreviation',
                'UTC'
            ),

            'current' => $current,

            'forecast' => $forecast,

            'risk' => $this->calculateRisk(
                $current,
                $forecast[0] ?? []
            ),
        ];
    }

    private function calculateRisk(
        array $current,
        array $today
    ): array {
        $score = 0;
        $factors = [];

        $precipitation = (float) (
            $today['precipitation_sum']
            ?? $current['precipitation']
            ?? 0
        );

        if ($precipitation >= 50) {
            $score += 35;

            $factors[] =
                'Curah hujan harian sangat tinggi.';
        } elseif ($precipitation >= 20) {
            $score += 22;

            $factors[] =
                'Curah hujan harian tergolong tinggi.';
        } elseif ($precipitation >= 5) {
            $score += 10;

            $factors[] =
                'Terdapat potensi hujan yang perlu dipantau.';
        }

        $windSpeed = max(
            (float) ($today['wind_speed_max'] ?? 0),
            (float) ($current['wind_speed'] ?? 0)
        );

        $windGusts = max(
            (float) ($today['wind_gusts_max'] ?? 0),
            (float) ($current['wind_gusts'] ?? 0)
        );

        $strongestWind = max(
            $windSpeed,
            $windGusts
        );

        if ($strongestWind >= 90) {
            $score += 35;

            $factors[] =
                'Hembusan angin berpotensi sangat berbahaya.';
        } elseif ($strongestWind >= 60) {
            $score += 25;

            $factors[] =
                'Hembusan angin tergolong kuat.';
        } elseif ($strongestWind >= 40) {
            $score += 12;

            $factors[] =
                'Kecepatan angin perlu dipantau.';
        }

        $weatherCode = (int) (
            $today['weather_code']
            ?? $current['weather_code']
            ?? 0
        );

        if (
            in_array(
                $weatherCode,
                [95, 96, 99],
                true
            )
        ) {
            $score += 20;

            $factors[] =
                'Terdapat indikator badai petir.';
        } elseif (
            in_array(
                $weatherCode,
                [65, 67, 75, 82, 86],
                true
            )
        ) {
            $score += 15;

            $factors[] =
                'Terdapat indikator hujan atau salju lebat.';
        } elseif (
            in_array(
                $weatherCode,
                [61, 63, 66, 71, 73, 80, 81, 85],
                true
            )
        ) {
            $score += 8;

            $factors[] =
                'Terdapat indikator cuaca kurang stabil.';
        }

        $maximumTemperature = $today['temperature_max']
            ?? $current['temperature']
            ?? null;

        $minimumTemperature = $today['temperature_min']
            ?? $current['temperature']
            ?? null;

        if (
            $maximumTemperature !== null
            && (float) $maximumTemperature >= 40
        ) {
            $score += 10;

            $factors[] =
                'Suhu maksimum tergolong ekstrem.';
        }

        if (
            $minimumTemperature !== null
            && (float) $minimumTemperature <= 0
        ) {
            $score += 8;

            $factors[] =
                'Suhu minimum berada pada titik beku.';
        }

        $score = min(100, $score);

        if ($score <= 30) {
            $level = 'low';
            $label = 'Low Risk';
        } elseif ($score <= 60) {
            $level = 'medium';
            $label = 'Medium Risk';
        } else {
            $level = 'high';
            $label = 'High Risk';
        }

        if (count($factors) === 0) {
            $factors[] =
                'Tidak ada indikator cuaca ekstrem yang dominan.';
        }

        return [
            'score' => $score,
            'level' => $level,
            'label' => $label,
            'factors' => $factors,
        ];
    }

    private function weatherDescription(
        ?int $code
    ): string {
        return match ($code) {
            0 => 'Cerah',

            1 => 'Sebagian besar cerah',

            2 => 'Berawan sebagian',

            3 => 'Mendung',

            45, 48 => 'Berkabut',

            51 => 'Gerimis ringan',

            53 => 'Gerimis sedang',

            55 => 'Gerimis lebat',

            56, 57 => 'Gerimis beku',

            61 => 'Hujan ringan',

            63 => 'Hujan sedang',

            65 => 'Hujan lebat',

            66, 67 => 'Hujan beku',

            71 => 'Salju ringan',

            73 => 'Salju sedang',

            75 => 'Salju lebat',

            77 => 'Butiran salju',

            80 => 'Hujan lokal ringan',

            81 => 'Hujan lokal sedang',

            82 => 'Hujan lokal lebat',

            85, 86 => 'Hujan salju',

            95 => 'Badai petir',

            96, 99 => 'Badai petir dengan hujan es',

            default => 'Kondisi tidak diketahui',
        };
    }

    private function toNumber(
        mixed $value
    ): ?float {
        return is_numeric($value)
            ? (float) $value
            : null;
    }

    private function toInteger(
        mixed $value
    ): ?int {
        return is_numeric($value)
            ? (int) $value
            : null;
    }
}