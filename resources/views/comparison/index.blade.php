@extends('layouts.app')

@section(
    'title',
    'Country Comparison - Global Supply Chain'
)

@push('styles')
    <style>
        .comparison-country-card {
            min-height: 320px;
        }

        .comparison-flag {
            width: 64px;
            height: 44px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .comparison-value {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .comparison-chart-container {
            position: relative;
            min-height: 320px;
        }

        .risk-component-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .risk-component-item {
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f8fafc;
        }

        .risk-component-name {
            display: block;
            margin-bottom: 4px;
            font-size: 0.78rem;
            color: #64748b;
        }

        .risk-component-score {
            font-size: 1rem;
            font-weight: 700;
        }

        .methodology-list {
            margin: 0;
            padding-left: 18px;
        }

        .methodology-list li {
            margin-bottom: 6px;
        }

        @media (max-width: 576px) {
            .risk-component-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="topbar">
        <div>
            <h2>Country Comparison Engine</h2>

            <p>
                Bandingkan GDP, inflation, risk, weather,
                dan currency dari dua negara.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                {{ $countries->count() }} Countries Available
            </span>
        </div>
    </div>

    <section class="dashboard-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label
                    for="countryA"
                    class="form-label"
                >
                    Negara pertama
                </label>

                <select
                    id="countryA"
                    class="form-select"
                >
                    @foreach ($countries as $country)
                        <option
                            value="{{ $country->id }}"
                            @selected(
                                $defaultCountryA === $country->id
                            )
                        >
                            {{ $country->name }}
                            ({{ $country->cca3 }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button
                    type="button"
                    id="swapCountries"
                    class="btn btn-outline-secondary w-100"
                >
                    Swap
                </button>
            </div>

            <div class="col-md-5">
                <label
                    for="countryB"
                    class="form-label"
                >
                    Negara kedua
                </label>

                <select
                    id="countryB"
                    class="form-select"
                >
                    @foreach ($countries as $country)
                        <option
                            value="{{ $country->id }}"
                            @selected(
                                $defaultCountryB === $country->id
                            )
                        >
                            {{ $country->name }}
                            ({{ $country->cca3 }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <button
                    type="button"
                    id="compareCountries"
                    class="btn btn-primary w-100"
                >
                    Compare Countries
                </button>
            </div>
        </div>
    </section>

    <div
        id="comparisonLoading"
        class="alert alert-info d-none"
    >
        Mengambil data ekonomi, cuaca, kurs, sentimen berita,
        dan menghitung risk score...
    </div>

    <div
        id="comparisonError"
        class="alert alert-danger d-none"
    ></div>

    <div
        id="comparisonResults"
        class="d-none"
    >
        <section class="row g-4 mb-4">
            <div class="col-xl-6">
                <div
                    id="countryACard"
                    class="dashboard-card comparison-country-card"
                ></div>
            </div>

            <div class="col-xl-6">
                <div
                    id="countryBCard"
                    class="dashboard-card comparison-country-card"
                ></div>
            </div>
        </section>

        <section class="dashboard-card mb-4">
            <div
                class="d-flex justify-content-between
                align-items-center mb-3"
            >
                <div>
                    <h3 class="mb-1">
                        Comparison Details
                    </h3>

                    <p class="text-secondary mb-0">
                        Data terbaru yang tersedia dari setiap sumber.
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr>
                            <th>Indicator</th>

                            <th id="countryAHeader">
                                Country A
                            </th>

                            <th id="countryBHeader">
                                Country B
                            </th>
                        </tr>
                    </thead>

                    <tbody id="comparisonTableBody"></tbody>
                </table>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-xl-7">
                <div class="dashboard-card">
                    <h3 class="mb-1">
                        Risk Score Comparison
                    </h3>

                    <p class="text-secondary">
                        Risk berasal dari Risk Scoring Engine
                        yang menggabungkan weather, inflation,
                        news sentiment, dan currency.
                    </p>

                    <div class="comparison-chart-container">
                        <canvas id="riskComparisonChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="dashboard-card h-100">
                    <h3 class="mb-3">
                        Comparison Result
                    </h3>

                    <div
                        id="comparisonRecommendation"
                        class="alert alert-primary"
                    ></div>

                    <div class="small text-secondary">
                        <strong>
                            Risk Scoring Methodology
                        </strong>

                        <ul
                            id="riskMethodologyList"
                            class="methodology-list mt-2"
                        >
                            <li>
                                Weather Risk: 30%
                            </li>

                            <li>
                                Inflation Risk: 20%
                            </li>

                            <li>
                                News Sentiment Risk: 40%
                            </li>

                            <li>
                                Currency Risk: 10%
                            </li>
                        </ul>

                        <p class="mb-0 mt-3">
                            Hasil merupakan estimasi analitik
                            Weighted Supply Chain Risk Model,
                            bukan peringkat risiko resmi.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script type="module">
        const comparisonApiUrl =
            @json(route('api.comparison.index'));

        const countryASelect =
            document.getElementById('countryA');

        const countryBSelect =
            document.getElementById('countryB');

        const compareButton =
            document.getElementById(
                'compareCountries'
            );

        const swapButton =
            document.getElementById(
                'swapCountries'
            );

        const loadingBox =
            document.getElementById(
                'comparisonLoading'
            );

        const errorBox =
            document.getElementById(
                'comparisonError'
            );

        const resultsBox =
            document.getElementById(
                'comparisonResults'
            );

        const countryACard =
            document.getElementById(
                'countryACard'
            );

        const countryBCard =
            document.getElementById(
                'countryBCard'
            );

        const tableBody =
            document.getElementById(
                'comparisonTableBody'
            );

        const recommendationBox =
            document.getElementById(
                'comparisonRecommendation'
            );

        const methodologyList =
            document.getElementById(
                'riskMethodologyList'
            );

        let riskChart = null;

        function escapeHtml(value) {
            if (
                value === null
                || value === undefined
            ) {
                return '';
            }

            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function safeUrl(value) {
            if (! value) {
                return '';
            }

            try {
                const url = new URL(value);

                if (
                    url.protocol === 'http:'
                    || url.protocol === 'https:'
                ) {
                    return url.href;
                }
            } catch (error) {
                return '';
            }

            return '';
        }

        function formatPopulation(value) {
            const number = Number(value);

            if (! Number.isFinite(number)) {
                return '-';
            }

            return number.toLocaleString('id-ID');
        }

        function formatGdp(indicator) {
            if (
                ! indicator
                || ! Number.isFinite(
                    Number(indicator.value)
                )
            ) {
                return '-';
            }

            const value =
                new Intl.NumberFormat(
                    'en-US',
                    {
                        style: 'currency',
                        currency: 'USD',
                        notation: 'compact',
                        maximumFractionDigits: 2,
                    }
                ).format(
                    Number(indicator.value)
                );

            return `${value} (${indicator.year ?? '-'})`;
        }

        function formatInflation(indicator) {
            if (
                ! indicator
                || ! Number.isFinite(
                    Number(indicator.value)
                )
            ) {
                return '-';
            }

            const value =
                Number(indicator.value)
                    .toLocaleString(
                        'id-ID',
                        {
                            maximumFractionDigits: 2,
                        }
                    );

            return `${value}% (${indicator.year ?? '-'})`;
        }

        function formatWeather(weather) {
            if (! weather?.current) {
                return '-';
            }

            const description =
                weather.current.description
                ?? 'Kondisi tidak diketahui';

            const temperatureValue =
                Number(
                    weather.current.temperature
                );

            const temperature =
                Number.isFinite(temperatureValue)
                    ? `${
                        temperatureValue.toLocaleString(
                            'id-ID',
                            {
                                maximumFractionDigits: 1,
                            }
                        )
                    }°C`
                    : '-';

            return `${
                escapeHtml(description)
            }, ${temperature}`;
        }

        function formatCurrency(currency) {
            if (
                ! currency
                || ! Number.isFinite(
                    Number(currency.rate)
                )
            ) {
                return '-';
            }

            const formattedRate =
                Number(currency.rate)
                    .toLocaleString(
                        'id-ID',
                        {
                            maximumFractionDigits: 6,
                        }
                    );

            const targetCurrency =
                escapeHtml(
                    currency.target_currency
                    ?? '-'
                );

            return `1 USD = ${formattedRate} ${targetCurrency}`;
        }

        function riskBadge(risk) {
            if (
                ! risk
                || risk.score === null
                || risk.score === undefined
            ) {
                return `
                    <span class="badge text-bg-secondary">
                        Data Unavailable
                    </span>
                `;
            }

            const score = escapeHtml(risk.score);

            if (risk.level === 'low') {
                return `
                    <span class="badge text-bg-success">
                        ${score} — Low Risk
                    </span>
                `;
            }

            if (risk.level === 'high') {
                return `
                    <span class="badge text-bg-danger">
                        ${score} — High Risk
                    </span>
                `;
            }

            if (risk.level === 'medium') {
                return `
                    <span class="badge text-bg-warning">
                        ${score} — Medium Risk
                    </span>
                `;
            }

            return `
                <span class="badge text-bg-secondary">
                    ${score} — ${escapeHtml(
                        risk.label ?? 'Unknown'
                    )}
                </span>
            `;
        }

        function riskComponent(
            component,
            fallbackName
        ) {
            const name =
                component?.name
                ?? fallbackName;

            const score =
                component?.score;

            const weight =
                component?.weight;

            const scoreText =
                score === null
                || score === undefined
                    ? 'Unavailable'
                    : `${escapeHtml(score)} / 100`;

            const weightText =
                Number.isFinite(Number(weight))
                    ? `${escapeHtml(weight)}% weight`
                    : '-';

            return `
                <div class="risk-component-item">
                    <span class="risk-component-name">
                        ${escapeHtml(name)}
                    </span>

                    <div class="risk-component-score">
                        ${scoreText}
                    </div>

                    <span class="small text-secondary">
                        ${weightText}
                    </span>
                </div>
            `;
        }

        function renderCountryCard(snapshot) {
            const country =
                snapshot.country ?? {};

            const risk =
                snapshot.risk ?? {};

            const components =
                risk.components ?? {};

            const flag =
                safeUrl(country.flag_url);

            const errors =
                Array.isArray(snapshot.errors)
                    ? snapshot.errors
                    : [];

            const availableWeight =
                Number.isFinite(
                    Number(risk.available_weight)
                )
                    ? `${risk.available_weight}% data weight available`
                    : null;

            return `
                <div
                    class="d-flex justify-content-between
                    align-items-start gap-3 mb-4"
                >
                    <div>
                        <h3 class="mb-1">
                            ${escapeHtml(
                                country.name ?? '-'
                            )}
                        </h3>

                        <p class="text-secondary mb-0">
                            ${escapeHtml(
                                country.official_name
                                ?? country.name
                                ?? '-'
                            )}
                        </p>
                    </div>

                    ${
                        flag
                            ? `
                                <img
                                    src="${flag}"
                                    alt="${escapeHtml(
                                        country.name ?? 'Country'
                                    )}"
                                    class="comparison-flag"
                                >
                            `
                            : ''
                    }
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <span class="text-secondary small">
                            Capital
                        </span>

                        <div class="comparison-value">
                            ${escapeHtml(
                                country.capital ?? '-'
                            )}
                        </div>
                    </div>

                    <div class="col-6">
                        <span class="text-secondary small">
                            Region
                        </span>

                        <div class="comparison-value">
                            ${escapeHtml(
                                country.region ?? '-'
                            )}
                        </div>
                    </div>

                    <div class="col-6">
                        <span class="text-secondary small">
                            Population
                        </span>

                        <div class="comparison-value">
                            ${formatPopulation(
                                country.population
                            )}
                        </div>
                    </div>

                    <div class="col-6">
                        <span class="text-secondary small">
                            Currency
                        </span>

                        <div class="comparison-value">
                            ${escapeHtml(
                                country.currency_code ?? '-'
                            )}
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        ${riskBadge(risk)}

                        ${
                            availableWeight
                                ? `
                                    <span
                                        class="small text-secondary ms-2"
                                    >
                                        ${escapeHtml(
                                            availableWeight
                                        )}
                                    </span>
                                `
                                : ''
                        }
                    </div>
                </div>

                <div class="risk-component-grid">
                    ${riskComponent(
                        components.weather,
                        'Weather Risk'
                    )}

                    ${riskComponent(
                        components.inflation,
                        'Inflation Risk'
                    )}

                    ${riskComponent(
                        components.news,
                        'News Sentiment Risk'
                    )}

                    ${riskComponent(
                        components.currency,
                        'Currency Risk'
                    )}
                </div>

                ${
                    errors.length > 0
                        ? `
                            <div
                                class="alert alert-warning
                                small mt-3 mb-0"
                            >
                                ${errors
                                    .map(escapeHtml)
                                    .join('<br>')}
                            </div>
                        `
                        : ''
                }
            `;
        }

        function comparisonRow(
            indicator,
            valueA,
            valueB
        ) {
            return `
                <tr>
                    <th>
                        ${escapeHtml(indicator)}
                    </th>

                    <td>${valueA}</td>
                    <td>${valueB}</td>
                </tr>
            `;
        }

        function renderComparisonTable(
            first,
            second
        ) {
            document.getElementById(
                'countryAHeader'
            ).textContent =
                first.country.name;

            document.getElementById(
                'countryBHeader'
            ).textContent =
                second.country.name;

            tableBody.innerHTML = [
                comparisonRow(
                    'GDP',
                    escapeHtml(
                        formatGdp(
                            first.economy?.gdp
                        )
                    ),
                    escapeHtml(
                        formatGdp(
                            second.economy?.gdp
                        )
                    )
                ),

                comparisonRow(
                    'Inflation',
                    escapeHtml(
                        formatInflation(
                            first.economy?.inflation
                        )
                    ),
                    escapeHtml(
                        formatInflation(
                            second.economy?.inflation
                        )
                    )
                ),

                comparisonRow(
                    'Risk Score',
                    riskBadge(first.risk),
                    riskBadge(second.risk)
                ),

                comparisonRow(
                    'Weather',
                    formatWeather(
                        first.weather
                    ),
                    formatWeather(
                        second.weather
                    )
                ),

                comparisonRow(
                    'Currency',
                    formatCurrency(
                        first.currency
                    ),
                    formatCurrency(
                        second.currency
                    )
                ),
            ].join('');
        }

        function renderRiskChart(
            first,
            second
        ) {
            const canvas =
                document.getElementById(
                    'riskComparisonChart'
                );

            if (riskChart) {
                riskChart.destroy();
            }

            riskChart = new window.Chart(
                canvas,
                {
                    type: 'bar',

                    data: {
                        labels: [
                            first.country.name,
                            second.country.name,
                        ],

                        datasets: [
                            {
                                label:
                                    'Supply Chain Risk Score',

                                data: [
                                    first.risk?.score ?? 0,
                                    second.risk?.score ?? 0,
                                ],

                                borderWidth: 1,
                            },
                        ],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                            },
                        },

                        plugins: {
                            legend: {
                                display: false,
                            },

                            tooltip: {
                                callbacks: {
                                    label(context) {
                                        return `Risk Score: ${
                                            context.raw
                                        }`;
                                    },
                                },
                            },
                        },
                    },
                }
            );
        }

        function renderMethodology(
            methodology
        ) {
            const weights =
                methodology?.weights ?? {};

            methodologyList.innerHTML = `
                <li>
                    Weather Risk:
                    ${escapeHtml(
                        weights.weather ?? 30
                    )}%
                </li>

                <li>
                    Inflation Risk:
                    ${escapeHtml(
                        weights.inflation ?? 20
                    )}%
                </li>

                <li>
                    News Sentiment Risk:
                    ${escapeHtml(
                        weights.news ?? 40
                    )}%
                </li>

                <li>
                    Currency Risk:
                    ${escapeHtml(
                        weights.currency ?? 10
                    )}%
                </li>
            `;
        }

        function setLoading(isLoading) {
            loadingBox.classList.toggle(
                'd-none',
                ! isLoading
            );

            compareButton.disabled =
                isLoading;

            swapButton.disabled =
                isLoading;

            countryASelect.disabled =
                isLoading;

            countryBSelect.disabled =
                isLoading;
        }

        function showError(message) {
            errorBox.textContent = message;

            errorBox.classList.remove(
                'd-none'
            );
        }

        function hideError() {
            errorBox.textContent = '';

            errorBox.classList.add(
                'd-none'
            );
        }

        async function loadComparison() {
            hideError();

            if (
                countryASelect.value
                === countryBSelect.value
            ) {
                showError(
                    'Pilih dua negara yang berbeda.'
                );

                resultsBox.classList.add(
                    'd-none'
                );

                return;
            }

            setLoading(true);

            const params =
                new URLSearchParams({
                    country_a:
                        countryASelect.value,

                    country_b:
                        countryBSelect.value,
                });

            const controller =
                new AbortController();

            const timeoutId =
                window.setTimeout(
                    () => controller.abort(),
                    25000
                );

            try {
                const response = await fetch(
                    `${comparisonApiUrl}?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },

                        signal: controller.signal,
                    }
                );

                const responseText =
                    await response.text();

                let result = null;

                if (
                    responseText.trim() !== ''
                ) {
                    try {
                        result =
                            JSON.parse(responseText);
                    } catch (parseError) {
                        throw new Error(
                            `Server mengembalikan respons `
                            + `yang bukan JSON. `
                            + `HTTP ${response.status}.`
                        );
                    }
                }

                if (! response.ok) {
                    throw new Error(
                        result?.detail
                        ?? result?.message
                        ?? `Server error HTTP ${
                            response.status
                        }.`
                    );
                }

                if (! result?.success) {
                    throw new Error(
                        result?.message
                        ?? 'Perbandingan gagal dimuat.'
                    );
                }

                const first =
                    result.data.country_a;

                const second =
                    result.data.country_b;

                countryACard.innerHTML =
                    renderCountryCard(first);

                countryBCard.innerHTML =
                    renderCountryCard(second);

                renderComparisonTable(
                    first,
                    second
                );

                renderRiskChart(
                    first,
                    second
                );

                renderMethodology(
                    result.data.methodology
                );

                recommendationBox.textContent =
                    result.data.recommendation
                        ?.message
                    ?? 'Kesimpulan belum tersedia.';

                resultsBox.classList.remove(
                    'd-none'
                );
            } catch (error) {
                resultsBox.classList.add(
                    'd-none'
                );

                if (
                    error.name === 'AbortError'
                ) {
                    showError(
                        'Permintaan melewati batas waktu. '
                        + 'Pastikan koneksi internet dan API aktif.'
                    );
                } else {
                    showError(
                        error.message
                        ?? 'Terjadi kesalahan.'
                    );
                }
            } finally {
                window.clearTimeout(timeoutId);

                setLoading(false);
            }
        }

        swapButton.addEventListener(
            'click',
            () => {
                const previousA =
                    countryASelect.value;

                countryASelect.value =
                    countryBSelect.value;

                countryBSelect.value =
                    previousA;

                loadComparison();
            }
        );

        compareButton.addEventListener(
            'click',
            loadComparison
        );

        loadComparison();
    </script>
@endpush