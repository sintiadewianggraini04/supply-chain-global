<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ExchangeRate;
use App\Models\RiskScoreSnapshot;
use App\Services\OpenMeteoService;
use App\Services\RiskScoringService;
use App\Services\WorldBankService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardApiController extends Controller
{
    public function index(
        Request $request,
        WorldBankService $worldBankService,
        OpenMeteoService $openMeteoService,
        RiskScoringService $riskScoringService
    ): JsonResponse {
        $validated = $request->validate([
            'country' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $countryReference = trim(
            (string) (
                $validated['country']
                ?? 'IDN'
            )
        );

        $country = $this->resolveCountry(
            $countryReference
        );

        if ($country === null) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Negara tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        $errors = [];

        $economyDataset = [
            'gdp' => null,
            'inflation' => null,

            'trends' => [
                'gdp' => [],
                'inflation' => [],
            ],

            'errors' => [],
        ];

        /*
         * Mengambil GDP, inflasi, GDP Trend,
         * dan Inflation Trend dari World Bank.
         */
        try {
            $economyDataset =
                $worldBankService
                    ->getCountryDashboardData(
                        (string) $country->cca3,
                        10
                    );
        } catch (Throwable $exception) {
            report($exception);

            $errors[] =
                'Data World Bank gagal diambil.';
        }

        $economy = [
            'gdp' =>
                $economyDataset['gdp']
                ?? null,

            'inflation' =>
                $economyDataset['inflation']
                ?? null,
        ];

        $economyErrors =
            $economyDataset['errors']
            ?? [];

        if (is_array($economyErrors)) {
            $errors = array_merge(
                $errors,
                $economyErrors
            );
        }

        /*
         * Mengambil data cuaca aktual.
         */
        $weather = null;

        if (
            is_numeric($country->latitude)
            && is_numeric($country->longitude)
        ) {
            try {
                $weather =
                    $openMeteoService
                        ->getForecast(
                            (float) $country->latitude,
                            (float) $country->longitude
                        );
            } catch (Throwable $exception) {
                report($exception);

                $errors[] =
                    'Data cuaca dari Open-Meteo tidak tersedia.';
            }
        } else {
            $errors[] =
                'Koordinat negara belum tersedia.';
        }

        /*
         * Menghitung weighted risk score.
         */
        try {
            $risk =
                $riskScoringService
                    ->scoreCountry(
                        $country,
                        $economy,
                        $weather ?? []
                    );
        } catch (Throwable $exception) {
            report($exception);

            $risk = [
                'score' => null,
                'level' => 'unknown',
                'label' => 'Data Unavailable',
                'available_weight' => 0,
                'components' => [],
                'errors' => [
                    'Risk score gagal dihitung.',
                ],
                'methodology' => null,
            ];

            $errors[] =
                'Risk score gagal dihitung.';
        }

        $riskErrors =
            $risk['errors']
            ?? [];

        if (is_array($riskErrors)) {
            $errors = array_merge(
                $errors,
                $riskErrors
            );
        }

        /*
         * Mengambil nilai tukar USD terhadap
         * mata uang negara yang dipilih.
         */
        $currency =
            $this->currencyData(
                $country
            );

        if (
            ! empty($currency['message'])
        ) {
            $errors[] =
                $currency['message'];
        }

        /*
         * Menyimpan satu snapshot per negara per hari.
         * Tidak akan menyebabkan error jika tabel
         * belum tersedia.
         */
        $this->storeRiskSnapshot(
            $country,
            $risk
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Dashboard data retrieved successfully.',

            'data' => [
                'country' => [
                    'id' => $country->id,
                    'name' => $country->name,

                    'official_name' =>
                        $country->official_name,

                    'cca2' => $country->cca2,
                    'cca3' => $country->cca3,

                    'capital' =>
                        $country->capital,

                    'region' =>
                        $country->region,

                    'subregion' =>
                        $country->subregion,

                    'population' =>
                        is_numeric(
                            $country->population
                        )
                            ? (int) $country->population
                            : null,

                    'currency_code' =>
                        $country->currency_code,

                    'currency_name' =>
                        $country->currency_name,

                    'currency_symbol' =>
                        $country->currency_symbol,

                    'latitude' =>
                        is_numeric(
                            $country->latitude
                        )
                            ? (float) $country->latitude
                            : null,

                    'longitude' =>
                        is_numeric(
                            $country->longitude
                        )
                            ? (float) $country->longitude
                            : null,

                    'flag_url' =>
                        $country->flag_url,
                ],

                'economy' => $economy,

                'currency' => $currency,

                'weather' => $weather,

                'risk' => $risk,

                'trends' => [
                    'gdp' => data_get(
                        $economyDataset,
                        'trends.gdp',
                        []
                    ),

                    'inflation' => data_get(
                        $economyDataset,
                        'trends.inflation',
                        []
                    ),

                    'currency' =>
                        $currency['series']
                        ?? [],

                    'risk' =>
                        $this->riskTrend(
                            $country
                        ),
                ],

                'countries' =>
                    $this->countryOptions(),

                'errors' => array_values(
                    array_unique(
                        array_filter($errors)
                    )
                ),

                'generated_at' =>
                    now()->toIso8601String(),
            ],
        ]);
    }

    private function resolveCountry(
        string $reference
    ): ?Country {
        $reference = trim($reference);

        $countryCode = strtoupper(
            $reference
        );

        return Country::query()
            ->where('is_active', true)
            ->where(
                function (
                    Builder $query
                ) use (
                    $reference,
                    $countryCode
                ) {
                    if (
                        ctype_digit(
                            $reference
                        )
                    ) {
                        $query->orWhere(
                            'id',
                            (int) $reference
                        );
                    }

                    $query
                        ->orWhere(
                            'cca2',
                            $countryCode
                        )
                        ->orWhere(
                            'cca3',
                            $countryCode
                        )
                        ->orWhere(
                            'name',
                            $reference
                        )
                        ->orWhere(
                            'official_name',
                            $reference
                        );
                }
            )
            ->first();
    }

    private function currencyData(
        Country $country
    ): array {
        $currencyCode = strtoupper(
            trim(
                (string) $country->currency_code
            )
        );

        $result = [
            'base_currency' => 'USD',

            'target_currency' =>
                $currencyCode ?: null,

            'currency_name' =>
                $country->currency_name,

            'currency_symbol' =>
                $country->currency_symbol,

            'latest_rate' => null,
            'latest_date' => null,

            'series' => [],

            'message' => null,
        ];

        if (
            preg_match(
                '/^[A-Z]{3}$/',
                $currencyCode
            ) !== 1
        ) {
            $result['message'] =
                'Kode mata uang negara tidak tersedia.';

            return $result;
        }

        /*
         * Amerika Serikat memakai USD sebagai
         * base currency sehingga nilainya selalu 1.
         */
        if ($currencyCode === 'USD') {
            $result['latest_rate'] = 1;

            $result['latest_date'] =
                now()->toDateString();

            $result['series'] = [
                [
                    'date' =>
                        now()->toDateString(),

                    'rate' => 1,
                ],
            ];

            return $result;
        }

        $latest = ExchangeRate::query()
            ->where(
                'base_currency',
                'USD'
            )
            ->where(
                'target_currency',
                $currencyCode
            )
            ->orderByDesc('rate_date')
            ->orderByDesc('fetched_at')
            ->first();

        if ($latest === null) {
            $result['message'] =
                "Kurs USD/{$currencyCode} belum tersimpan.";

            return $result;
        }

        $latestDate =
            $this->dateString(
                $latest->rate_date
            );

        $records = ExchangeRate::query()
            ->where(
                'base_currency',
                'USD'
            )
            ->where(
                'target_currency',
                $currencyCode
            )
            ->orderByDesc('rate_date')
            ->orderByDesc('fetched_at')
            ->limit(90)
            ->get()
            ->unique(
                function (
                    ExchangeRate $rate
                ): string {
                    return $this->dateString(
                        $rate->rate_date
                    ) ?? 'rate-' . $rate->id;
                }
            )
            ->take(30)
            ->sortBy(
                function (
                    ExchangeRate $rate
                ): string {
                    return $this->dateString(
                        $rate->rate_date
                    ) ?? '';
                }
            )
            ->values();

        $result['latest_rate'] =
            (float) $latest->rate;

        $result['latest_date'] =
            $latestDate;

        $result['series'] =
            $records
                ->map(
                    function (
                        ExchangeRate $rate
                    ): array {
                        return [
                            'date' =>
                                $this->dateString(
                                    $rate->rate_date
                                ),

                            'rate' =>
                                (float) $rate->rate,
                        ];
                    }
                )
                ->filter(
                    fn (array $item): bool =>
                        $item['date'] !== null
                )
                ->values()
                ->all();

        return $result;
    }

    private function storeRiskSnapshot(
        Country $country,
        array $risk
    ): void {
        /*
         * Dashboard tetap berjalan walaupun
         * migration snapshot belum dijalankan.
         */
        if (
            ! Schema::hasTable(
                'risk_score_snapshots'
            )
        ) {
            return;
        }

        $finalScore =
            $risk['score']
            ?? null;

        if (! is_numeric($finalScore)) {
            return;
        }

        try {
            RiskScoreSnapshot::query()
                ->updateOrCreate(
                    [
                        'country_id' =>
                            $country->id,

                        'recorded_on' =>
                            now()->toDateString(),
                    ],

                    [
                        'weather_score' =>
                            $this->nullableScore(
                                data_get(
                                    $risk,
                                    'components.weather.score'
                                )
                            ),

                        'inflation_score' =>
                            $this->nullableScore(
                                data_get(
                                    $risk,
                                    'components.inflation.score'
                                )
                            ),

                        'news_score' =>
                            $this->nullableScore(
                                data_get(
                                    $risk,
                                    'components.news.score'
                                )
                            ),

                        'currency_score' =>
                            $this->nullableScore(
                                data_get(
                                    $risk,
                                    'components.currency.score'
                                )
                            ),

                        'final_score' =>
                            max(
                                0,
                                min(
                                    100,
                                    (int) round(
                                        $finalScore
                                    )
                                )
                            ),

                        'level' =>
                            (string) (
                                $risk['level']
                                ?? 'unknown'
                            ),
                    ]
                );
        } catch (Throwable $exception) {
            /*
             * Gagal menyimpan snapshot tidak boleh
             * menyebabkan seluruh dashboard gagal.
             */
            report($exception);
        }
    }

    private function riskTrend(
        Country $country
    ): array {
        /*
         * Jika tabel belum ada, kembalikan array
         * kosong agar grafik menampilkan empty state.
         */
        if (
            ! Schema::hasTable(
                'risk_score_snapshots'
            )
        ) {
            return [];
        }

        try {
            return RiskScoreSnapshot::query()
                ->where(
                    'country_id',
                    $country->id
                )
                ->orderByDesc(
                    'recorded_on'
                )
                ->limit(30)
                ->get()
                ->sortBy(
                    function (
                        RiskScoreSnapshot $snapshot
                    ): string {
                        return $this->dateString(
                            $snapshot->recorded_on
                        ) ?? '';
                    }
                )
                ->values()
                ->map(
                    function (
                        RiskScoreSnapshot $snapshot
                    ): array {
                        return [
                            'date' =>
                                $this->dateString(
                                    $snapshot->recorded_on
                                ),

                            'score' =>
                                (int) $snapshot->final_score,

                            'level' =>
                                $snapshot->level,
                        ];
                    }
                )
                ->filter(
                    fn (array $item): bool =>
                        $item['date'] !== null
                )
                ->values()
                ->all();
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function nullableScore(
        mixed $score
    ): ?int {
        if (! is_numeric($score)) {
            return null;
        }

        return max(
            0,
            min(
                100,
                (int) round($score)
            )
        );
    }

    private function countryOptions(): array
    {
        return Country::query()
            ->where('is_active', true)
            ->whereNotNull('cca3')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'cca2',
                'cca3',
                'capital',
            ])
            ->map(
                fn (
                    Country $country
                ): array => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'cca2' => $country->cca2,
                    'cca3' => $country->cca3,

                    'capital' =>
                        $country->capital,
                ]
            )
            ->values()
            ->all();
    }

    private function dateString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse(
                $value
            )->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}