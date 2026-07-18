@extends('layouts.app')

@section('title', 'Dashboard - Global Supply Chain')

@push('styles')
    <style>
        .country-summary-grid {
            display: grid;
            grid-template-columns:
                repeat(5, minmax(0, 1fr));
            gap: 18px;
        }

        .country-summary-grid .metric-card {
            min-width: 0;
        }

        .summary-card-gdp {
            --metric-accent: #2563eb;
            --metric-soft: rgba(37, 99, 235, 0.08);
        }

        .summary-card-inflation {
            --metric-accent: #f43f5e;
            --metric-soft: rgba(244, 63, 94, 0.08);
        }

        .summary-card-population {
            --metric-accent: #8b5cf6;
            --metric-soft: rgba(139, 92, 246, 0.08);
        }

        .summary-card-currency {
            --metric-accent: #f59e0b;
            --metric-soft: rgba(245, 158, 11, 0.09);
        }

        .summary-card-weather {
            --metric-accent: #14b8a6;
            --metric-soft: rgba(20, 184, 166, 0.09);
        }

        .currency-rate-value {
            font-size: 23px;
            line-height: 1.25;
        }

        .visualization-title {
            margin-bottom: 18px;
        }

        .visualization-title h3 {
            margin: 0 0 5px;
            color: #172033;
            font-size: 20px;
            font-weight: 750;
        }

        .visualization-title p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
        }

        .visualization-chart {
            position: relative;
            width: 100%;
            height: 315px;
        }

        .visualization-chart-large {
            height: 350px;
        }

        .chart-empty {
            display: flex;
            height: 100%;
            align-items: center;
            justify-content: center;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            color: #64748b;
            background-color: #f8fafc;
            font-size: 13px;
            text-align: center;
        }

        .risk-trend-header {
            display: flex;
            margin-bottom: 18px;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .risk-trend-header h3 {
            margin-bottom: 5px;
        }

        .risk-trend-header p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
        }

        .risk-score-badge {
            display: inline-flex;
            min-width: 115px;
            padding: 8px 12px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 750;
        }

        @media (max-width: 1399px) {
            .country-summary-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .country-summary-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .country-summary-grid {
                grid-template-columns: 1fr;
            }

            .risk-trend-header {
                flex-direction: column;
            }

            .visualization-chart,
            .visualization-chart-large {
                height: 280px;
            }
        }
    </style>
@endpush

@section('content')
    <header class="topbar dashboard-topbar">
        <div class="dashboard-heading">
            <span class="page-eyebrow">
                Country Overview
            </span>

            <h2>
                Global Country Dashboard
            </h2>

            <p>
                GDP, inflasi, populasi, mata uang,
                cuaca, dan tren risiko negara.
            </p>
        </div>

        <div class="dashboard-toolbar">
            <label for="countrySelector">
                Monitoring Country
            </label>

            <select
                id="countrySelector"
                class="form-select"
            >
                <option value="IDN">
                    Indonesia
                </option>
            </select>
        </div>
    </header>

    <div
        id="dashboardLoading"
        class="alert alert-info"
    >
        Mengambil data dashboard...
    </div>

    <div
        id="dashboardError"
        class="alert alert-danger d-none"
    ></div>

    <div
        id="dashboardWarning"
        class="alert alert-warning d-none"
    ></div>

    <section class="country-summary-grid mb-4">
        <article
            class="
                dashboard-card
                metric-card
                summary-card-gdp
            "
        >
            <div class="metric-card-header">
                <span class="metric-label">
                    GDP
                </span>

                <span class="metric-chip metric-chip-primary">
                    World Bank
                </span>
            </div>

            <p
                id="dashboardGdp"
                class="metric-value"
            >
                -
            </p>

            <div class="metric-card-footer">
                <span>
                    Gross Domestic Product
                </span>

                <strong id="dashboardGdpYear">
                    -
                </strong>
            </div>
        </article>

        <article
            class="
                dashboard-card
                metric-card
                summary-card-inflation
            "
        >
            <div class="metric-card-header">
                <span class="metric-label">
                    Inflation
                </span>

                <span class="metric-chip metric-chip-danger">
                    Annual
                </span>
            </div>

            <p
                id="dashboardInflation"
                class="metric-value"
            >
                -
            </p>

            <div class="metric-card-footer">
                <span>
                    Consumer price change
                </span>

                <strong id="dashboardInflationYear">
                    -
                </strong>
            </div>
        </article>

        <article
            class="
                dashboard-card
                metric-card
                summary-card-population
            "
        >
            <div class="metric-card-header">
                <span class="metric-label">
                    Population
                </span>

                <span class="metric-chip metric-chip-primary">
                    Country
                </span>
            </div>

            <p
                id="dashboardPopulation"
                class="metric-value"
            >
                -
            </p>

            <div class="metric-card-footer">
                <span id="dashboardRegion">
                    -
                </span>

                <strong>
                    People
                </strong>
            </div>
        </article>

        <article
            class="
                dashboard-card
                metric-card
                summary-card-currency
            "
        >
            <div class="metric-card-header">
                <span class="metric-label">
                    Currency
                </span>

                <span
                    id="dashboardCurrencyCode"
                    class="metric-chip metric-chip-warning"
                >
                    -
                </span>
            </div>

            <p
                id="dashboardCurrencyRate"
                class="
                    metric-value
                    currency-rate-value
                "
            >
                -
            </p>

            <div class="metric-card-footer">
                <span id="dashboardCurrencyName">
                    -
                </span>

                <strong id="dashboardCurrencyDate">
                    -
                </strong>
            </div>
        </article>

        <article
            class="
                dashboard-card
                metric-card
                summary-card-weather
            "
        >
            <div class="metric-card-header">
                <span class="metric-label">
                    Current Weather
                </span>

                <span class="metric-chip metric-chip-success">
                    Live
                </span>
            </div>

            <p
                id="dashboardTemperature"
                class="metric-value"
            >
                -
            </p>

            <div class="metric-card-footer">
                <span id="dashboardWeatherCondition">
                    -
                </span>

                <strong id="dashboardWind">
                    -
                </strong>
            </div>
        </article>
    </section>

    <section class="dashboard-card mb-4">
        <div class="risk-trend-header">
            <div>
                <span class="section-eyebrow">
                    Country Risk
                </span>

                <h3>
                    Risk Trend
                </h3>

                <p>
                    Perubahan weighted supply chain
                    risk score dari waktu ke waktu.
                </p>
            </div>

            <span
                id="currentRiskBadge"
                class="
                    risk-score-badge
                    text-bg-secondary
                "
            >
                Data unavailable
            </span>
        </div>

        <div
            id="riskTrendContainer"
            class="
                visualization-chart
                visualization-chart-large
            "
        >
            <canvas id="riskTrendChart"></canvas>

            <div
                id="riskTrendEmpty"
                class="chart-empty d-none"
            >
                Riwayat risk score belum tersedia.
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="visualization-title">
            <span class="section-eyebrow">
                Historical Analytics
            </span>

            <h3>
                Data Visualization Dashboard
            </h3>

            <p>
                Grafik GDP, inflasi, dan perubahan
                kurs negara yang dipilih.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <article class="dashboard-card">
                    <div class="chart-card-header">
                        <div>
                            <h3>
                                GDP Trend
                            </h3>

                            <p>
                                Perkembangan GDP tahunan
                                berdasarkan World Bank.
                            </p>
                        </div>
                    </div>

                    <div class="visualization-chart">
                        <canvas id="gdpTrendChart"></canvas>

                        <div
                            id="gdpTrendEmpty"
                            class="chart-empty d-none"
                        >
                            Data GDP trend belum tersedia.
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-xl-6">
                <article class="dashboard-card">
                    <div class="chart-card-header">
                        <div>
                            <h3>
                                Inflation Trend
                            </h3>

                            <p>
                                Perubahan tingkat inflasi
                                tahunan.
                            </p>
                        </div>
                    </div>

                    <div class="visualization-chart">
                        <canvas id="inflationTrendChart"></canvas>

                        <div
                            id="inflationTrendEmpty"
                            class="chart-empty d-none"
                        >
                            Data inflation trend belum tersedia.
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-12">
                <article class="dashboard-card">
                    <div class="chart-card-header">
                        <div>
                            <h3>
                                Currency Trend
                            </h3>

                            <p id="currencyTrendDescription">
                                Perubahan nilai tukar terhadap USD.
                            </p>
                        </div>
                    </div>

                    <div
                        class="
                            visualization-chart
                            visualization-chart-large
                        "
                    >
                        <canvas id="currencyTrendChart"></canvas>

                        <div
                            id="currencyTrendEmpty"
                            class="chart-empty d-none"
                        >
                            Riwayat kurs belum tersedia.
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const dashboardApiUrl =
            @json(route('api.dashboard.index'));

        const countrySelector =
            document.getElementById(
                'countrySelector'
            );

        const loadingBox =
            document.getElementById(
                'dashboardLoading'
            );

        const errorBox =
            document.getElementById(
                'dashboardError'
            );

        const warningBox =
            document.getElementById(
                'dashboardWarning'
            );

        const charts = {
            gdp: null,
            inflation: null,
            currency: null,
            risk: null,
        };

        let countryOptionsLoaded = false;

        function formatNumber(
            value,
            decimals = 2
        ) {
            if (
                value === null
                || value === undefined
                || Number.isNaN(Number(value))
            ) {
                return '-';
            }

            return new Intl.NumberFormat(
                'id-ID',
                {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                }
            ).format(Number(value));
        }

        function formatCompact(
            value,
            locale = 'id-ID'
        ) {
            if (
                value === null
                || value === undefined
                || Number.isNaN(Number(value))
            ) {
                return '-';
            }

            return new Intl.NumberFormat(
                locale,
                {
                    notation: 'compact',
                    maximumFractionDigits: 2,
                }
            ).format(Number(value));
        }

        function formatGdp(value) {
            if (
                value === null
                || value === undefined
                || Number.isNaN(Number(value))
            ) {
                return '-';
            }

            return new Intl.NumberFormat(
                'en-US',
                {
                    style: 'currency',
                    currency: 'USD',
                    notation: 'compact',
                    maximumFractionDigits: 2,
                }
            ).format(Number(value));
        }

        function formatDate(value) {
            if (! value) {
                return '-';
            }

            return new Date(
                `${value}T00:00:00`
            ).toLocaleDateString(
                'id-ID',
                {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }
            );
        }

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

        function setLoading(isLoading) {
            loadingBox.classList.toggle(
                'd-none',
                ! isLoading
            );

            countrySelector.disabled =
                isLoading;
        }

        function hideError() {
            errorBox.textContent = '';

            errorBox.classList.add(
                'd-none'
            );
        }

        function showError(message) {
            errorBox.textContent = message;

            errorBox.classList.remove(
                'd-none'
            );
        }

        function renderWarnings(errors) {
            if (
                ! Array.isArray(errors)
                || errors.length === 0
            ) {
                warningBox.innerHTML = '';

                warningBox.classList.add(
                    'd-none'
                );

                return;
            }

            warningBox.innerHTML =
                errors
                    .map(escapeHtml)
                    .join('<br>');

            warningBox.classList.remove(
                'd-none'
            );
        }

        function renderCountryOptions(
            countries,
            selectedCode
        ) {
            if (
                ! countryOptionsLoaded
                && Array.isArray(countries)
                && countries.length > 0
            ) {
                countrySelector.innerHTML =
                    countries
                        .map((country) => `
                            <option
                                value="${escapeHtml(
                                    country.cca3
                                )}"
                            >
                                ${escapeHtml(
                                    country.name
                                )}

                                ${
                                    country.capital
                                        ? ` — ${escapeHtml(
                                            country.capital
                                        )}`
                                        : ''
                                }
                            </option>
                        `)
                        .join('');

                countryOptionsLoaded = true;
            }

            countrySelector.value =
                selectedCode;
        }

        function renderSummary(data) {
            const country =
                data.country;

            const economy =
                data.economy ?? {};

            const currency =
                data.currency ?? {};

            const weather =
                data.weather;

            document.getElementById(
                'dashboardGdp'
            ).textContent =
                formatGdp(
                    economy.gdp?.value
                );

            document.getElementById(
                'dashboardGdpYear'
            ).textContent =
                economy.gdp?.year
                ?? '-';

            document.getElementById(
                'dashboardInflation'
            ).textContent =
                economy.inflation?.value
                !== null
                && economy.inflation?.value
                !== undefined
                    ? `${formatNumber(
                        economy.inflation.value,
                        2
                    )}%`
                    : '-';

            document.getElementById(
                'dashboardInflationYear'
            ).textContent =
                economy.inflation?.year
                ?? '-';

            document.getElementById(
                'dashboardPopulation'
            ).textContent =
                formatCompact(
                    country.population
                );

            document.getElementById(
                'dashboardRegion'
            ).textContent =
                [
                    country.region,
                    country.subregion,
                ]
                    .filter(Boolean)
                    .join(' — ')
                || '-';

            const currencyCode =
                currency.target_currency
                ?? country.currency_code
                ?? '-';

            document.getElementById(
                'dashboardCurrencyCode'
            ).textContent =
                currencyCode;

            if (
                currency.latest_rate !== null
                && currency.latest_rate !== undefined
            ) {
                const decimals =
                    Number(currency.latest_rate) < 10
                        ? 4
                        : 2;

                document.getElementById(
                    'dashboardCurrencyRate'
                ).textContent =
                    `1 USD = ${
                        formatNumber(
                            currency.latest_rate,
                            decimals
                        )
                    } ${currencyCode}`;
            } else {
                document.getElementById(
                    'dashboardCurrencyRate'
                ).textContent =
                    currencyCode;
            }

            document.getElementById(
                'dashboardCurrencyName'
            ).textContent =
                currency.currency_name
                ?? country.currency_name
                ?? 'Currency';

            document.getElementById(
                'dashboardCurrencyDate'
            ).textContent =
                currency.latest_date
                    ? formatDate(
                        currency.latest_date
                    )
                    : 'Kurs belum tersedia';

            document.getElementById(
                'dashboardTemperature'
            ).textContent =
                weather?.current?.temperature
                !== null
                && weather?.current?.temperature
                !== undefined
                    ? `${formatNumber(
                        weather.current.temperature,
                        1
                    )}°C`
                    : '-';

            document.getElementById(
                'dashboardWeatherCondition'
            ).textContent =
                weather?.current?.description
                ?? 'Data tidak tersedia';

            document.getElementById(
                'dashboardWind'
            ).textContent =
                weather?.current?.wind_speed
                !== null
                && weather?.current?.wind_speed
                !== undefined
                    ? `${formatNumber(
                        weather.current.wind_speed,
                        1
                    )} km/h`
                    : '-';

            document.getElementById(
                'currencyTrendDescription'
            ).textContent =
                `Perubahan nilai tukar USD terhadap ${currencyCode}.`;

            renderRiskBadge(
                data.risk
            );
        }

        function renderRiskBadge(risk) {
            const badge =
                document.getElementById(
                    'currentRiskBadge'
                );

            badge.classList.remove(
                'text-bg-secondary',
                'text-bg-success',
                'text-bg-warning',
                'text-bg-danger'
            );

            if (
                ! risk
                || risk.score === null
                || risk.score === undefined
            ) {
                badge.classList.add(
                    'text-bg-secondary'
                );

                badge.textContent =
                    'Data unavailable';

                return;
            }

            if (risk.level === 'low') {
                badge.classList.add(
                    'text-bg-success'
                );
            } else if (
                risk.level === 'high'
            ) {
                badge.classList.add(
                    'text-bg-danger'
                );
            } else {
                badge.classList.add(
                    'text-bg-warning'
                );
            }

            badge.textContent =
                `${risk.score}/100 · ${risk.label}`;
        }

        function destroyChart(key) {
            if (charts[key]) {
                charts[key].destroy();
                charts[key] = null;
            }
        }

        function renderLineChart({
            key,
            canvasId,
            emptyId,
            labels,
            values,
            label,
            borderColor,
            backgroundColor,
            beginAtZero = false,
            maximum = undefined,
            tickCallback = undefined,
            tooltipCallback = undefined,
        }) {
            destroyChart(key);

            const canvas =
                document.getElementById(
                    canvasId
                );

            const empty =
                document.getElementById(
                    emptyId
                );

            const hasData =
                Array.isArray(labels)
                && Array.isArray(values)
                && labels.length > 0
                && values.length > 0;

            canvas.classList.toggle(
                'd-none',
                ! hasData
            );

            empty.classList.toggle(
                'd-none',
                hasData
            );

            if (! hasData) {
                return;
            }

            charts[key] =
                new window.Chart(
                    canvas,
                    {
                        type: 'line',

                        data: {
                            labels,

                            datasets: [
                                {
                                    label,

                                    data: values,

                                    borderColor,

                                    backgroundColor,

                                    borderWidth: 2.5,

                                    fill: true,

                                    tension: 0.35,

                                    spanGaps: true,

                                    pointRadius:
                                        values.length === 1
                                            ? 5
                                            : 2,

                                    pointHoverRadius: 5,

                                    pointBackgroundColor:
                                        '#ffffff',

                                    pointBorderColor:
                                        borderColor,

                                    pointBorderWidth: 2,
                                },
                            ],
                        },

                        options: {
                            responsive: true,

                            maintainAspectRatio:
                                false,

                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },

                            scales: {
                                x: {
                                    border: {
                                        display: false,
                                    },

                                    grid: {
                                        display: false,
                                    },

                                    ticks: {
                                        color: '#64748b',
                                    },
                                },

                                y: {
                                    beginAtZero,

                                    max: maximum,

                                    border: {
                                        display: false,
                                    },

                                    grid: {
                                        color:
                                            'rgba(148, 163, 184, 0.18)',
                                    },

                                    ticks: {
                                        color: '#64748b',

                                        callback:
                                            tickCallback,
                                    },
                                },
                            },

                            plugins: {
                                legend: {
                                    display: false,
                                },

                                tooltip: {
                                    backgroundColor:
                                        '#0f172a',

                                    titleColor:
                                        '#ffffff',

                                    bodyColor:
                                        '#e2e8f0',

                                    padding: 11,

                                    displayColors: false,

                                    callbacks: {
                                        label:
                                            tooltipCallback,
                                    },
                                },
                            },
                        },
                    }
                );
        }

        function renderCharts(data) {
            const trends =
                data.trends ?? {};

            const gdp =
                Array.isArray(trends.gdp)
                    ? trends.gdp
                    : [];

            renderLineChart({
                key: 'gdp',

                canvasId:
                    'gdpTrendChart',

                emptyId:
                    'gdpTrendEmpty',

                labels:
                    gdp.map(
                        (item) => item.year
                    ),

                values:
                    gdp.map(
                        (item) => item.value
                    ),

                label: 'GDP',

                borderColor:
                    '#2563eb',

                backgroundColor:
                    'rgba(37, 99, 235, 0.10)',

                tickCallback(value) {
                    return formatCompact(
                        value,
                        'en-US'
                    );
                },

                tooltipCallback(context) {
                    return `GDP: ${
                        formatGdp(
                            context.parsed.y
                        )
                    }`;
                },
            });

            const inflation =
                Array.isArray(
                    trends.inflation
                )
                    ? trends.inflation
                    : [];

            renderLineChart({
                key: 'inflation',

                canvasId:
                    'inflationTrendChart',

                emptyId:
                    'inflationTrendEmpty',

                labels:
                    inflation.map(
                        (item) => item.year
                    ),

                values:
                    inflation.map(
                        (item) => item.value
                    ),

                label: 'Inflation',

                borderColor:
                    '#f43f5e',

                backgroundColor:
                    'rgba(244, 63, 94, 0.09)',

                tickCallback(value) {
                    return `${formatNumber(
                        value,
                        1
                    )}%`;
                },

                tooltipCallback(context) {
                    return `Inflation: ${
                        formatNumber(
                            context.parsed.y,
                            2
                        )
                    }%`;
                },
            });

            const currency =
                Array.isArray(
                    trends.currency
                )
                    ? trends.currency
                    : [];

            const currencyCode =
                data.currency
                    ?.target_currency
                ?? data.country
                    ?.currency_code
                ?? '';

            renderLineChart({
                key: 'currency',

                canvasId:
                    'currencyTrendChart',

                emptyId:
                    'currencyTrendEmpty',

                labels:
                    currency.map(
                        (item) =>
                            formatDate(
                                item.date
                            )
                    ),

                values:
                    currency.map(
                        (item) => item.rate
                    ),

                label:
                    `USD/${currencyCode}`,

                borderColor:
                    '#f59e0b',

                backgroundColor:
                    'rgba(245, 158, 11, 0.10)',

                tickCallback(value) {
                    return formatCompact(
                        value
                    );
                },

                tooltipCallback(context) {
                    return `1 USD = ${
                        formatNumber(
                            context.parsed.y,
                            4
                        )
                    } ${currencyCode}`;
                },
            });

            const risk =
                Array.isArray(trends.risk)
                    ? trends.risk
                    : [];

            renderLineChart({
                key: 'risk',

                canvasId:
                    'riskTrendChart',

                emptyId:
                    'riskTrendEmpty',

                labels:
                    risk.map(
                        (item) =>
                            formatDate(
                                item.date
                            )
                    ),

                values:
                    risk.map(
                        (item) => item.score
                    ),

                label: 'Risk Score',

                borderColor:
                    '#8b5cf6',

                backgroundColor:
                    'rgba(139, 92, 246, 0.10)',

                beginAtZero: true,

                maximum: 100,

                tickCallback(value) {
                    return value;
                },

                tooltipCallback(context) {
                    return `Risk score: ${
                        context.parsed.y
                    }/100`;
                },
            });
        }

        function renderDashboard(data) {
            renderCountryOptions(
                data.countries,
                data.country.cca3
            );

            renderSummary(data);

            renderCharts(data);

            renderWarnings(
                data.errors
            );
        }

        async function loadDashboard(
            countryCode = null
        ) {
            hideError();
            setLoading(true);

            const selectedCountry =
                countryCode
                ?? countrySelector.value
                ?? 'IDN';

            const parameters =
                new URLSearchParams({
                    country:
                        selectedCountry,
                });

            try {
                const response =
                    await fetch(
                        `${dashboardApiUrl}?${parameters.toString()}`,
                        {
                            headers: {
                                Accept:
                                    'application/json',
                            },
                        }
                    );

                const result =
                    await response.json();

                if (
                    ! response.ok
                    || ! result.success
                ) {
                    throw new Error(
                        result.message
                        ?? 'Dashboard gagal dimuat.'
                    );
                }

                renderDashboard(
                    result.data
                );
            } catch (error) {
                showError(
                    error.message
                    ?? 'Terjadi kesalahan.'
                );
            } finally {
                setLoading(false);
            }
        }

        countrySelector.addEventListener(
            'change',
            () => {
                loadDashboard(
                    countrySelector.value
                );
            }
        );

        loadDashboard('IDN');
    </script>
@endpush