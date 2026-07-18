<?php

namespace App\Services;

use App\Models\Country;
use App\Models\ExchangeRate;
use App\Models\NewsArticle;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RiskScoringService
{
    private const WEATHER_WEIGHT = 30;

    private const INFLATION_WEIGHT = 20;

    private const NEWS_WEIGHT = 40;

    private const CURRENCY_WEIGHT = 10;

    public function __construct(
        private readonly WorldBankService $worldBankService,
        private readonly OpenMeteoService $openMeteoService
    ) {
    }

    public function scoreCountry(
        Country $country,
        ?array $economy = null,
        ?array $weather = null
    ): array {
        $errors = [];

        if ($economy === null) {
            try {
                $economy = $this->worldBankService
                    ->getCountryIndicators(
                        (string) $country->cca3
                    );
            } catch (Throwable $exception) {
                report($exception);

                $economy = [
                    'gdp' => null,
                    'inflation' => null,
                ];

                $errors[] =
                    'Data inflasi World Bank tidak tersedia.';
            }
        }

        if ($weather === null) {
            if (
                is_numeric($country->latitude)
                && is_numeric($country->longitude)
            ) {
                try {
                    $weather = Cache::remember(
                        'risk-weather:'
                            . strtolower(
                                (string) $country->cca3
                            ),
                        now()->addMinutes(30),
                        fn () => $this->openMeteoService
                            ->getForecast(
                                (float) $country->latitude,
                                (float) $country->longitude
                            )
                    );
                } catch (Throwable $exception) {
                    report($exception);

                    $errors[] =
                        'Data risiko cuaca tidak tersedia.';
                }
            } else {
                $errors[] =
                    'Koordinat negara tidak tersedia.';
            }
        }

        $components = [
            'weather' => $this->buildWeatherComponent(
                $weather
            ),

            'inflation' => $this->buildInflationComponent(
                data_get($economy, 'inflation')
            ),

            'news' => $this->buildNewsComponent(
                $country
            ),

            'currency' => $this->buildCurrencyComponent(
                $country->currency_code
            ),
        ];

        $weightedTotal = 0;
        $availableWeight = 0;

        foreach ($components as $component) {
            $score = $component['score'] ?? null;
            $weight = $component['weight'] ?? 0;

            if (! is_numeric($score)) {
                continue;
            }

            $weightedTotal +=
                (float) $score * (float) $weight;

            $availableWeight += (float) $weight;
        }

        if ($availableWeight <= 0) {
            return [
                'score' => null,
                'level' => 'unknown',
                'label' => 'Data Unavailable',
                'available_weight' => 0,
                'components' => $components,
                'errors' => array_values(
                    array_unique($errors)
                ),

                'methodology' =>
                    $this->methodology(),
            ];
        }

        $finalScore = (int) round(
            $weightedTotal / $availableWeight
        );

        foreach ($components as &$component) {
            if (
                is_numeric($component['score'] ?? null)
                && $availableWeight > 0
            ) {
                $component['effective_weight'] = round(
                    (
                        (float) $component['weight']
                        / $availableWeight
                    ) * 100,
                    2
                );
            } else {
                $component['effective_weight'] = 0;
            }
        }

        unset($component);

        [
            $level,
            $label,
        ] = $this->riskLevel($finalScore);

        return [
            'score' => $finalScore,
            'level' => $level,
            'label' => $label,

            'available_weight' =>
                (int) $availableWeight,

            'components' => $components,

            'errors' => array_values(
                array_unique($errors)
            ),

            'methodology' =>
                $this->methodology(),
        ];
    }

    private function buildWeatherComponent(
        ?array $weather
    ): array {
        $score = data_get(
            $weather,
            'risk.score'
        );

        return [
            'name' => 'Weather Risk',
            'source' => 'Open-Meteo',
            'weight' => self::WEATHER_WEIGHT,

            'score' => is_numeric($score)
                ? max(
                    0,
                    min(100, (int) round($score))
                )
                : null,

            'details' => [
                'weather' => data_get(
                    $weather,
                    'current.description'
                ),

                'temperature' => data_get(
                    $weather,
                    'current.temperature'
                ),

                'precipitation' => data_get(
                    $weather,
                    'current.precipitation'
                ),

                'wind_speed' => data_get(
                    $weather,
                    'current.wind_speed'
                ),

                'factors' => data_get(
                    $weather,
                    'risk.factors',
                    []
                ),
            ],
        ];
    }

    private function buildInflationComponent(
        ?array $inflation
    ): array {
        $inflationValue =
            $inflation['value'] ?? null;

        return [
            'name' => 'Inflation Risk',
            'source' => 'World Bank',
            'weight' => self::INFLATION_WEIGHT,

            'score' => is_numeric($inflationValue)
                ? $this->calculateInflationRisk(
                    (float) $inflationValue
                )
                : null,

            'details' => [
                'value' => is_numeric($inflationValue)
                    ? round(
                        (float) $inflationValue,
                        4
                    )
                    : null,

                'year' =>
                    $inflation['year'] ?? null,

                'indicator' =>
                    $inflation['indicator'] ?? null,
            ],
        ];
    }

    private function buildNewsComponent(
        Country $country
    ): array {
        return Cache::remember(
            'risk-news-country:' . $country->id,
            now()->addMinutes(20),
            function () use ($country) {
                $baseQuery = NewsArticle::query()
                    ->with('sentiment')
                    ->whereHas('sentiment')
                    ->where(
                        'published_at',
                        '>=',
                        now()->subDays(30)
                    );

                $terms = array_values(
                    array_unique(
                        array_filter([
                            trim(
                                (string) $country->name
                            ),

                            trim(
                                (string) $country->official_name
                            ),

                            trim(
                                (string) $country->cca3
                            ),
                        ])
                    )
                );

                $countryQuery = clone $baseQuery;

                if ($terms !== []) {
                    $countryQuery->where(
                        function ($query) use ($terms) {
                            foreach ($terms as $term) {
                                $query
                                    ->orWhere(
                                        'title',
                                        'like',
                                        "%{$term}%"
                                    )
                                    ->orWhere(
                                        'description',
                                        'like',
                                        "%{$term}%"
                                    )
                                    ->orWhere(
                                        'content',
                                        'like',
                                        "%{$term}%"
                                    );
                            }
                        }
                    );
                }

                $articles = $countryQuery
                    ->latest('published_at')
                    ->limit(100)
                    ->get();

                $scope = 'country';

                /*
                 * Jika belum ada berita yang secara eksplisit
                 * menyebut negara, gunakan sentimen berita
                 * supply chain global sebagai fallback.
                 */
                if ($articles->isEmpty()) {
                    $articles = $baseQuery
                        ->latest('published_at')
                        ->limit(100)
                        ->get();

                    $scope = 'global_fallback';
                }

                $labels = $articles
                    ->map(
                        fn ($article) =>
                            $article->sentiment
                                ?->sentiment_label
                    )
                    ->filter()
                    ->values();

                $positive = $labels
                    ->filter(
                        fn ($label) =>
                            $label === 'positive'
                    )
                    ->count();

                $neutral = $labels
                    ->filter(
                        fn ($label) =>
                            $label === 'neutral'
                    )
                    ->count();

                $negative = $labels
                    ->filter(
                        fn ($label) =>
                            $label === 'negative'
                    )
                    ->count();

                $total =
                    $positive
                    + $neutral
                    + $negative;

                /*
                 * Negative memiliki kontribusi risiko penuh.
                 * Neutral memiliki kontribusi setengah.
                 * Positive tidak menambah risiko.
                 */
                $score = $total > 0
                    ? (int) round(
                        (
                            $negative
                            + ($neutral * 0.5)
                        ) / $total * 100
                    )
                    : null;

                return [
                    'name' =>
                        'News Sentiment Risk',

                    'source' =>
                        'GNews + Dictionary Sentiment',

                    'weight' =>
                        self::NEWS_WEIGHT,

                    'score' => $score,

                    'details' => [
                        'scope' => $scope,
                        'period_days' => 30,
                        'sample_size' => $total,

                        'positive' => $positive,
                        'neutral' => $neutral,
                        'negative' => $negative,

                        'positive_percentage' =>
                            $total > 0
                                ? round(
                                    $positive
                                    / $total
                                    * 100,
                                    2
                                )
                                : 0,

                        'neutral_percentage' =>
                            $total > 0
                                ? round(
                                    $neutral
                                    / $total
                                    * 100,
                                    2
                                )
                                : 0,

                        'negative_percentage' =>
                            $total > 0
                                ? round(
                                    $negative
                                    / $total
                                    * 100,
                                    2
                                )
                                : 0,
                    ],
                ];
            }
        );
    }

    private function buildCurrencyComponent(
        ?string $currencyCode
    ): array {
        $currencyCode = strtoupper(
            trim((string) $currencyCode)
        );

        $baseComponent = [
            'name' => 'Currency Risk',
            'source' => 'ExchangeRate-API',
            'weight' => self::CURRENCY_WEIGHT,
            'score' => null,
            'details' => [
                'currency_code' =>
                    $currencyCode ?: null,

                'message' => null,
            ],
        ];

        if (
            preg_match(
                '/^[A-Z]{3}$/',
                $currencyCode
            ) !== 1
        ) {
            $baseComponent['details']['message'] =
                'Kode mata uang tidak tersedia.';

            return $baseComponent;
        }

        if ($currencyCode === 'USD') {
            $baseComponent['score'] = 0;

            $baseComponent['details'] = [
                'currency_code' => 'USD',
                'latest_rate' => 1,
                'previous_rate' => 1,
                'change_percentage' => 0,
                'direction' => 'stable',
                'message' =>
                    'USD merupakan base currency.',
            ];

            return $baseComponent;
        }

        $latestRate = ExchangeRate::query()
            ->where('base_currency', 'USD')
            ->where(
                'target_currency',
                $currencyCode
            )
            ->orderByDesc('rate_date')
            ->orderByDesc('fetched_at')
            ->first();

        if ($latestRate === null) {
            $baseComponent['details']['message'] =
                'Data kurs terbaru tidak tersedia.';

            return $baseComponent;
        }

        $previousRate = ExchangeRate::query()
            ->where('base_currency', 'USD')
            ->where(
                'target_currency',
                $currencyCode
            )
            ->whereDate(
                'rate_date',
                '<',
                $latestRate->rate_date
                    ->toDateString()
            )
            ->orderByDesc('rate_date')
            ->orderByDesc('fetched_at')
            ->first();

        if (
            $previousRate === null
            || (float) $previousRate->rate === 0.0
        ) {
            $baseComponent['details'] = [
                'currency_code' => $currencyCode,

                'latest_rate' =>
                    (float) $latestRate->rate,

                'latest_date' =>
                    $latestRate->rate_date
                        ?->toDateString(),

                'previous_rate' => null,
                'change_percentage' => null,

                'message' =>
                    'Riwayat kurs sebelumnya belum tersedia.',
            ];

            return $baseComponent;
        }

        $latestValue =
            (float) $latestRate->rate;

        $previousValue =
            (float) $previousRate->rate;

        $changePercentage = (
            ($latestValue - $previousValue)
            / $previousValue
        ) * 100;

        $absoluteChange =
            abs($changePercentage);

        $baseComponent['score'] =
            $this->calculateCurrencyRisk(
                $absoluteChange
            );

        $baseComponent['details'] = [
            'currency_code' => $currencyCode,

            'latest_rate' =>
                $latestValue,

            'latest_date' =>
                $latestRate->rate_date
                    ?->toDateString(),

            'previous_rate' =>
                $previousValue,

            'previous_date' =>
                $previousRate->rate_date
                    ?->toDateString(),

            'change_percentage' =>
                round($changePercentage, 4),

            'absolute_change_percentage' =>
                round($absoluteChange, 4),

            'direction' => match (true) {
                $changePercentage > 0 =>
                    'increase',

                $changePercentage < 0 =>
                    'decrease',

                default => 'stable',
            },

            'message' => null,
        ];

        return $baseComponent;
    }

    private function calculateInflationRisk(
        float $inflation
    ): int {
        if ($inflation < -3) {
            return 70;
        }

        if ($inflation < 0) {
            return 40;
        }

        if ($inflation <= 2) {
            return 10;
        }

        if ($inflation <= 5) {
            return 25;
        }

        if ($inflation <= 10) {
            return 50;
        }

        if ($inflation <= 20) {
            return 75;
        }

        return 100;
    }

    private function calculateCurrencyRisk(
        float $changePercentage
    ): int {
        if ($changePercentage < 1) {
            return 10;
        }

        if ($changePercentage < 3) {
            return 25;
        }

        if ($changePercentage < 5) {
            return 45;
        }

        if ($changePercentage < 10) {
            return 70;
        }

        return 100;
    }

    private function riskLevel(
        int $score
    ): array {
        if ($score <= 30) {
            return [
                'low',
                'Low Risk',
            ];
        }

        if ($score <= 60) {
            return [
                'medium',
                'Medium Risk',
            ];
        }

        return [
            'high',
            'High Risk',
        ];
    }

    private function methodology(): array
    {
        return [
            'model' =>
                'Weighted Supply Chain Risk Model',

            'weights' => [
                'weather' =>
                    self::WEATHER_WEIGHT,

                'inflation' =>
                    self::INFLATION_WEIGHT,

                'news' =>
                    self::NEWS_WEIGHT,

                'currency' =>
                    self::CURRENCY_WEIGHT,
            ],

            'description' =>
                'Skor merupakan estimasi risiko '
                . 'gabungan dari cuaca, inflasi, '
                . 'sentimen berita, dan perubahan kurs.',

            'missing_data_policy' =>
                'Jika suatu komponen belum tersedia, '
                . 'bobot komponen yang tersedia '
                . 'dinormalisasi secara otomatis.',
        ];
    }
}