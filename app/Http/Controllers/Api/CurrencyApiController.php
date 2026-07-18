<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CurrencyApiController extends Controller
{
    public function index(
        Request $request,
        ExchangeRateService $exchangeRateService
    ): JsonResponse {
        $validated = $request->validate([
            'base' => [
                'nullable',
                'string',
                'size:3',
            ],

            'target' => [
                'nullable',
                'string',
                'size:3',
            ],

            'days' => [
                'nullable',
                'integer',
                'in:7,30,90,365',
            ],

            'refresh' => [
                'nullable',
                'boolean',
            ],
        ]);

        $base = strtoupper(
            trim(
                (string) (
                    $validated['base']
                    ?? 'USD'
                )
            )
        );

        $target = strtoupper(
            trim(
                (string) (
                    $validated['target']
                    ?? 'IDR'
                )
            )
        );

        $days = (int) (
            $validated['days']
            ?? 30
        );

        $forceRefresh = $request->boolean(
            'refresh'
        );

        try {
            /*
             * Mengambil nilai kurs terbaru dari
             * ExchangeRate-API Open Access.
             */
            $snapshot = $exchangeRateService
                ->latest(
                    $base,
                    $forceRefresh
                );

            /*
             * Ketika mata uang asal dan tujuan sama,
             * nilai tukarnya selalu 1.
             */
            $latestRate = $base === $target
                ? 1.0
                : data_get(
                    $snapshot,
                    "rates.{$target}"
                );

            if (! is_numeric($latestRate)) {
                return response()->json([
                    'success' => false,

                    'message' =>
                        "Nilai tukar {$base}/{$target} "
                        . 'tidak tersedia.',
                ], 404);
            }

            $latestRate = (float) $latestRate;

            /*
             * Mengambil riwayat snapshot kurs
             * yang tersimpan di database.
             */
            $history = $exchangeRateService
                ->pairHistory(
                    $base,
                    $target,
                    $days
                );

            /*
             * Menghapus snapshot dengan tanggal yang sama
             * agar tidak terjadi data ganda, kemudian
             * memasukkan nilai terbaru dari API.
             */
            $history = $history
                ->reject(
                    fn (array $point): bool =>
                        ($point['date'] ?? null)
                        === ($snapshot['rate_date'] ?? null)
                )
                ->push([
                    'date' =>
                        $snapshot['rate_date']
                        ?? now()->toDateString(),

                    'rate' =>
                        round(
                            $latestRate,
                            10
                        ),

                    'provider' =>
                        $snapshot['provider']
                        ?? 'ExchangeRate-API Open Access',

                    'fetched_at' =>
                        $snapshot['fetched_at']
                        ?? now()->toISOString(),
                ])
                ->sortBy('date')
                ->values();

            /*
             * Mengambil nilai sebelum nilai terbaru untuk
             * menghitung perubahan kurs.
             */
            $previousPoint =
                $history->count() >= 2
                    ? $history->get(
                        $history->count() - 2
                    )
                    : null;

            $previousValue = data_get(
                $previousPoint,
                'rate'
            );

            $previousRate =
                is_numeric($previousValue)
                    ? (float) $previousValue
                    : null;

            $changePercentage = null;

            if (
                $previousRate !== null
                && $previousRate !== 0.0
            ) {
                $changePercentage = (
                    ($latestRate - $previousRate)
                    / $previousRate
                ) * 100;
            }

            $direction = match (true) {
                $changePercentage === null =>
                    'unavailable',

                $changePercentage > 0 =>
                    'increase',

                $changePercentage < 0 =>
                    'decrease',

                default =>
                    'stable',
            };

            /*
             * PERBAIKAN ERROR:
             *
             * Jangan menggunakan:
             * ->filter('is_numeric')
             *
             * Collection Laravel memberikan dua parameter
             * ke callback, yaitu value dan key.
             */
            $values = $history
                ->pluck('rate')
                ->filter(
                    fn ($value): bool =>
                        is_numeric($value)
                )
                ->map(
                    fn ($value): float =>
                        (float) $value
                )
                ->values();

            return response()->json([
                'success' => true,

                'message' =>
                    'Nilai tukar terbaru berhasil dimuat.',

                'data' => [
                    'base_currency' =>
                        $base,

                    'target_currency' =>
                        $target,

                    'latest_rate' =>
                        round(
                            $latestRate,
                            10
                        ),

                    'inverse_rate' =>
                        $latestRate !== 0.0
                            ? round(
                                1 / $latestRate,
                                10
                            )
                            : null,

                    'previous_rate' =>
                        $previousRate !== null
                            ? round(
                                $previousRate,
                                10
                            )
                            : null,

                    'change_percentage' =>
                        $changePercentage !== null
                            ? round(
                                $changePercentage,
                                4
                            )
                            : null,

                    'direction' =>
                        $direction,

                    'highest_rate' =>
                        $values->isNotEmpty()
                            ? round(
                                (float) $values->max(),
                                10
                            )
                            : null,

                    'lowest_rate' =>
                        $values->isNotEmpty()
                            ? round(
                                (float) $values->min(),
                                10
                            )
                            : null,

                    'average_rate' =>
                        $values->isNotEmpty()
                            ? round(
                                (float) $values->average(),
                                10
                            )
                            : null,

                    'period_days' =>
                        $days,

                    'point_count' =>
                        $history->count(),

                    'rate_date' =>
                        $snapshot['rate_date']
                        ?? now()->toDateString(),

                    'provider' =>
                        $snapshot['provider']
                        ?? 'ExchangeRate-API Open Access',

                    'provider_updated_at' =>
                        $snapshot['provider_updated_at']
                        ?? null,

                    'provider_next_update_at' =>
                        $snapshot['provider_next_update_at']
                        ?? null,

                    'fetched_at' =>
                        $snapshot['fetched_at']
                        ?? now()->toISOString(),

                    'history' =>
                        $history->values(),

                    'currencies' =>
                        $exchangeRateService
                            ->currencyCodes()
                            ->values(),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,

                'message' =>
                    'Data nilai tukar gagal dimuat.',

                'detail' =>
                    config('app.debug')
                        ? $exception->getMessage()
                        : null,
            ], 500);
        }
    }
}