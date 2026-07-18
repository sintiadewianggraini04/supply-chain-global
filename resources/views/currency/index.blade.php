@extends('layouts.app')

@section(
    'title',
    'Currency Impact Dashboard - Supply Chain Global'
)

@push('styles')
    <style>
        .currency-toolbar {
            position: relative;
            overflow: hidden;
        }

        .currency-toolbar::after {
            content: '';
            position: absolute;
            top: -120px;
            right: -100px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            pointer-events: none;
            background: radial-gradient(
                circle,
                rgba(37, 99, 235, 0.18),
                rgba(37, 99, 235, 0)
            );
        }

        .currency-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            color: #047857;
            background: #ecfdf5;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .currency-live-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 5px
                rgba(16, 185, 129, 0.13);
        }

        .currency-stat-card {
            position: relative;
            min-height: 168px;
            overflow: hidden;
        }

        .currency-stat-label {
            display: block;
            margin-bottom: 13px;
            color: #64748b;
            font-size: 0.92rem;
        }

        .currency-stat-value {
            color: #12233d;
            font-size: clamp(
                1.45rem,
                2.4vw,
                2.15rem
            );
            font-weight: 750;
            line-height: 1.2;
            word-break: break-word;
        }

        .currency-stat-note {
            display: block;
            margin-top: 12px;
            color: #64748b;
            font-size: 0.84rem;
        }

        .currency-trend-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .currency-trend-up {
            color: #047857;
            background: #d1fae5;
        }

        .currency-trend-down {
            color: #b91c1c;
            background: #fee2e2;
        }

        .currency-trend-neutral {
            color: #475569;
            background: #e2e8f0;
        }

        .currency-chart-card {
            position: relative;
            min-height: 540px;
            overflow: hidden;
        }

        .currency-chart-wrapper {
            position: relative;
            width: 100%;
            min-height: 410px;
        }

        .currency-pair-label {
            display: inline-flex;
            align-items: center;
            padding: 7px 13px;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            color: #1d4ed8;
            background: #eff6ff;
            font-weight: 700;
        }

        .currency-converter-result {
            min-height: 58px;
            display: flex;
            align-items: center;
            padding: 13px 15px;
            border: 1px solid #dbe5f0;
            border-radius: 12px;
            color: #12233d;
            background: #f8fafc;
            font-size: 1.25rem;
            font-weight: 750;
        }

        .currency-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: grid;
            place-items: center;
            background:
                rgba(255, 255, 255, 0.84);
            backdrop-filter: blur(5px);
        }

        .currency-loading-overlay.d-none {
            display: none;
        }

        .currency-rate-number {
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .currency-mini-stat {
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            background: #f8fafc;
        }

        .currency-mini-stat span {
            display: block;
            margin-bottom: 7px;
            color: #64748b;
            font-size: 0.82rem;
        }

        .currency-mini-stat strong {
            color: #12233d;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .currency-chart-card {
                min-height: 470px;
            }

            .currency-chart-wrapper {
                min-height: 320px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="topbar">
        <div>
            <h2>Currency Impact Dashboard</h2>

            <p>
                Nilai tukar terbaru dan grafik perubahan
                kurs dari ExchangeRate-API.
            </p>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <div class="currency-live-badge">
                <span class="currency-live-dot"></span>
                Latest API Rate
            </div>

            <button
                type="button"
                id="refreshLiveCurrency"
                class="btn btn-primary"
            >
                Refresh API
            </button>
        </div>
    </div>

    <div
        id="currencyError"
        class="alert alert-danger d-none"
    ></div>

    <section
        class="dashboard-card currency-toolbar mb-4"
    >
        <div class="mb-4">
            <h3 class="mb-1">
                Currency Pair
            </h3>

            <p class="text-secondary mb-0">
                Pilih mata uang dan periode grafik.
            </p>
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label
                    for="baseCurrency"
                    class="form-label"
                >
                    Mata Uang Asal
                </label>

                <select
                    id="baseCurrency"
                    class="form-select"
                >
                    @foreach ($currencies as $currency)
                        <option
                            value="{{ $currency }}"
                            @selected(
                                $currency === $defaultBase
                            )
                        >
                            {{ $currency }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1">
                <button
                    type="button"
                    id="swapCurrencies"
                    class="btn btn-outline-secondary w-100"
                    title="Tukar pasangan mata uang"
                >
                    ⇄
                </button>
            </div>

            <div class="col-md-3">
                <label
                    for="targetCurrency"
                    class="form-label"
                >
                    Mata Uang Tujuan
                </label>

                <select
                    id="targetCurrency"
                    class="form-select"
                >
                    @foreach ($currencies as $currency)
                        <option
                            value="{{ $currency }}"
                            @selected(
                                $currency === $defaultTarget
                            )
                        >
                            {{ $currency }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label
                    for="currencyPeriod"
                    class="form-label"
                >
                    Periode
                </label>

                <select
                    id="currencyPeriod"
                    class="form-select"
                >
                    <option value="7">
                        7 Hari
                    </option>

                    <option
                        value="30"
                        selected
                    >
                        30 Hari
                    </option>

                    <option value="90">
                        3 Bulan
                    </option>

                    <option value="365">
                        1 Tahun
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <button
                    type="button"
                    id="showCurrency"
                    class="btn btn-primary w-100"
                >
                    Tampilkan
                </button>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div
                class="dashboard-card
                currency-stat-card h-100"
            >
                <span class="currency-stat-label">
                    Nilai Tukar Terbaru
                </span>

                <div
                    id="latestCurrencyRate"
                    class="currency-stat-value"
                >
                    -
                </div>

                <span
                    id="latestCurrencyDate"
                    class="currency-stat-note"
                >
                    Menunggu data API
                </span>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div
                class="dashboard-card
                currency-stat-card h-100"
            >
                <span class="currency-stat-label">
                    Kurs Sebaliknya
                </span>

                <div
                    id="inverseCurrencyRate"
                    class="currency-stat-value"
                >
                    -
                </div>

                <span class="currency-stat-note">
                    Nilai inverse pasangan mata uang
                </span>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div
                class="dashboard-card
                currency-stat-card h-100"
            >
                <span class="currency-stat-label">
                    Perubahan Terakhir
                </span>

                <div
                    id="currencyChange"
                    class="currency-stat-value"
                >
                    -
                </div>

                <div
                    id="currencyTrend"
                    class="currency-trend-pill
                    currency-trend-neutral"
                >
                    Menunggu riwayat
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div
                class="dashboard-card
                currency-stat-card h-100"
            >
                <span class="currency-stat-label">
                    Provider Update
                </span>

                <div
                    id="providerUpdateDate"
                    class="currency-stat-value"
                    style="font-size: 1.25rem;"
                >
                    -
                </div>

                <span
                    id="nextProviderUpdate"
                    class="currency-stat-note"
                >
                    ExchangeRate-API
                </span>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-xl-8">
            <div
                class="dashboard-card
                currency-chart-card h-100"
            >
                <div
                    id="currencyLoading"
                    class="currency-loading-overlay d-none"
                >
                    <div class="text-center">
                        <div
                            class="spinner-border
                            text-primary mb-3"
                            role="status"
                        ></div>

                        <div>
                            Mengambil kurs terbaru...
                        </div>
                    </div>
                </div>

                <div
                    class="d-flex justify-content-between
                    align-items-start gap-3 mb-4"
                >
                    <div>
                        <h3 class="mb-1">
                            Grafik Perubahan Kurs
                        </h3>

                        <p
                            id="currencyChartDescription"
                            class="text-secondary mb-0"
                        >
                            Riwayat nilai tukar.
                        </p>
                    </div>

                    <div
                        id="currencyPairLabel"
                        class="currency-pair-label"
                    >
                        USD / IDR
                    </div>
                </div>

                <div class="currency-chart-wrapper">
                    <canvas id="currencyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dashboard-card h-100">
                <div class="mb-4">
                    <h3 class="mb-1">
                        Currency Converter
                    </h3>

                    <p class="text-secondary mb-0">
                        Konversi menggunakan kurs API terbaru.
                    </p>
                </div>

                <div class="mb-3">
                    <label
                        for="currencyAmount"
                        class="form-label"
                    >
                        Jumlah
                    </label>

                    <div class="input-group">
                        <input
                            type="number"
                            id="currencyAmount"
                            class="form-control"
                            value="1"
                            min="0"
                            step="any"
                        >

                        <span
                            id="converterBaseCurrency"
                            class="input-group-text"
                        >
                            USD
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        Hasil Konversi
                    </label>

                    <div
                        id="currencyConversionResult"
                        class="currency-converter-result"
                    >
                        -
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <div class="currency-mini-stat">
                            <span>
                                Tertinggi pada periode
                            </span>

                            <strong id="highestCurrencyRate">
                                -
                            </strong>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="currency-mini-stat">
                            <span>
                                Terendah pada periode
                            </span>

                            <strong id="lowestCurrencyRate">
                                -
                            </strong>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="currency-mini-stat">
                            <span>
                                Rata-rata periode
                            </span>

                            <strong id="averageCurrencyRate">
                                -
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-card">
        <div
            class="d-flex justify-content-between
            align-items-center mb-3"
        >
            <div>
                <h3 class="mb-1">
                    Riwayat Nilai Tukar
                </h3>

                <p class="text-secondary mb-0">
                    Snapshot kurs yang tersimpan di database.
                </p>
            </div>

            <span
                id="currencyPointCount"
                class="badge text-bg-light fs-6"
            >
                0 data
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pasangan</th>
                        <th>Nilai Tukar</th>
                        <th>Provider</th>
                    </tr>
                </thead>

                <tbody id="currencyHistoryBody">
                    <tr>
                        <td
                            colspan="4"
                            class="text-center text-secondary"
                        >
                            Belum ada data.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const currencyApiUrl =
            @json(route('api.currency.index'));

        const baseCurrency =
            document.getElementById(
                'baseCurrency'
            );

        const targetCurrency =
            document.getElementById(
                'targetCurrency'
            );

        const currencyPeriod =
            document.getElementById(
                'currencyPeriod'
            );

        const showCurrencyButton =
            document.getElementById(
                'showCurrency'
            );

        const refreshButton =
            document.getElementById(
                'refreshLiveCurrency'
            );

        const swapButton =
            document.getElementById(
                'swapCurrencies'
            );

        const loadingBox =
            document.getElementById(
                'currencyLoading'
            );

        const errorBox =
            document.getElementById(
                'currencyError'
            );

        const amountInput =
            document.getElementById(
                'currencyAmount'
            );

        let chart = null;
        let currentData = null;

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

        function formatRate(value) {
            const number = Number(value);

            if (! Number.isFinite(number)) {
                return '-';
            }

            let maximumFractionDigits = 6;

            if (Math.abs(number) >= 1000) {
                maximumFractionDigits = 2;
            } else if (
                Math.abs(number) < 1
            ) {
                maximumFractionDigits = 8;
            }

            return number.toLocaleString(
                'id-ID',
                {
                    maximumFractionDigits,
                }
            );
        }

        function formatDate(value) {
            if (! value) {
                return '-';
            }

            const date = new Date(value);

            if (
                Number.isNaN(
                    date.getTime()
                )
            ) {
                return value;
            }

            return date.toLocaleDateString(
                'id-ID',
                {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }
            );
        }

        function formatDateTime(value) {
            if (! value) {
                return '-';
            }

            const date = new Date(value);

            if (
                Number.isNaN(
                    date.getTime()
                )
            ) {
                return value;
            }

            return date.toLocaleString(
                'id-ID',
                {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }
            );
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

        function setLoading(isLoading) {
            loadingBox.classList.toggle(
                'd-none',
                ! isLoading
            );

            showCurrencyButton.disabled =
                isLoading;

            refreshButton.disabled =
                isLoading;

            swapButton.disabled =
                isLoading;

            baseCurrency.disabled =
                isLoading;

            targetCurrency.disabled =
                isLoading;

            currencyPeriod.disabled =
                isLoading;
        }

        function updateConversion() {
            if (! currentData) {
                return;
            }

            const amount =
                Number(amountInput.value);

            const validAmount =
                Number.isFinite(amount)
                && amount >= 0
                    ? amount
                    : 0;

            const result =
                validAmount
                * Number(
                    currentData.latest_rate
                );

            document.getElementById(
                'converterBaseCurrency'
            ).textContent =
                currentData.base_currency;

            document.getElementById(
                'currencyConversionResult'
            ).textContent =
                `${formatRate(result)} ${
                    currentData.target_currency
                }`;
        }

        function renderTrend(data) {
            const valueElement =
                document.getElementById(
                    'currencyChange'
                );

            const trendElement =
                document.getElementById(
                    'currencyTrend'
                );

            if (
                data.change_percentage
                === null
                || data.change_percentage
                === undefined
            ) {
                valueElement.textContent = '-';

                trendElement.textContent =
                    'Butuh minimal 2 tanggal';

                trendElement.className =
                    'currency-trend-pill '
                    + 'currency-trend-neutral';

                return;
            }

            const change =
                Number(
                    data.change_percentage
                );

            valueElement.textContent =
                `${change >= 0 ? '+' : ''}${
                    change.toLocaleString(
                        'id-ID',
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 4,
                        }
                    )
                }%`;

            if (data.direction === 'increase') {
                trendElement.textContent =
                    '↑ Kurs meningkat';

                trendElement.className =
                    'currency-trend-pill '
                    + 'currency-trend-up';

                return;
            }

            if (data.direction === 'decrease') {
                trendElement.textContent =
                    '↓ Kurs menurun';

                trendElement.className =
                    'currency-trend-pill '
                    + 'currency-trend-down';

                return;
            }

            trendElement.textContent =
                '→ Kurs stabil';

            trendElement.className =
                'currency-trend-pill '
                + 'currency-trend-neutral';
        }

        function chartGradient(context) {
            const chartObject =
                context.chart;

            const area =
                chartObject.chartArea;

            if (! area) {
                return 'rgba(37, 99, 235, 0.12)';
            }

            const gradient =
                chartObject.ctx
                    .createLinearGradient(
                        0,
                        area.top,
                        0,
                        area.bottom
                    );

            gradient.addColorStop(
                0,
                'rgba(37, 99, 235, 0.38)'
            );

            gradient.addColorStop(
                0.55,
                'rgba(59, 130, 246, 0.14)'
            );

            gradient.addColorStop(
                1,
                'rgba(255, 255, 255, 0)'
            );

            return gradient;
        }

        function renderChart(data) {
            if (! window.Chart) {
                showError(
                    'Chart.js belum dimuat. '
                    + 'Periksa resources/js/app.js.'
                );

                return;
            }

            const canvas =
                document.getElementById(
                    'currencyChart'
                );

            const labels =
                data.history.map(
                    (point) =>
                        formatDate(point.date)
                );

            const values =
                data.history.map(
                    (point) =>
                        Number(point.rate)
                );

            if (chart) {
                chart.destroy();
            }

            chart = new window.Chart(
                canvas,
                {
                    type: 'line',

                    data: {
                        labels,

                        datasets: [
                            {
                                label:
                                    `${data.base_currency}/${data.target_currency}`,

                                data: values,

                                borderColor: '#2563eb',

                                backgroundColor:
                                    chartGradient,

                                borderWidth: 3,

                                fill: true,

                                tension: 0.36,

                                cubicInterpolationMode:
                                    'monotone',

                                pointRadius:
                                    values.length <= 15
                                        ? 3
                                        : 0,

                                pointHoverRadius: 7,

                                pointBackgroundColor:
                                    '#ffffff',

                                pointBorderColor:
                                    '#2563eb',

                                pointBorderWidth: 2,

                                segment: {
                                    borderColor(context) {
                                        const previous =
                                            context.p0
                                                .parsed.y;

                                        const current =
                                            context.p1
                                                .parsed.y;

                                        if (
                                            current > previous
                                        ) {
                                            return '#16a34a';
                                        }

                                        if (
                                            current < previous
                                        ) {
                                            return '#ef4444';
                                        }

                                        return '#2563eb';
                                    },
                                },
                            },
                        ],
                    },

                    options: {
                        responsive: true,

                        maintainAspectRatio: false,

                        animation: {
                            duration: 900,
                            easing: 'easeOutQuart',
                        },

                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },

                        plugins: {
                            legend: {
                                display: false,
                            },

                            tooltip: {
                                displayColors: false,

                                backgroundColor:
                                    'rgba(15, 23, 42, 0.95)',

                                titleColor: '#ffffff',

                                bodyColor: '#ffffff',

                                padding: 13,

                                cornerRadius: 10,

                                callbacks: {
                                    label(context) {
                                        return `1 ${
                                            data.base_currency
                                        } = ${
                                            formatRate(
                                                context.raw
                                            )
                                        } ${
                                            data.target_currency
                                        }`;
                                    },
                                },
                            },
                        },

                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },

                                border: {
                                    display: false,
                                },

                                ticks: {
                                    color: '#64748b',
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 8,
                                },
                            },

                            y: {
                                beginAtZero: false,

                                border: {
                                    display: false,
                                },

                                grid: {
                                    color:
                                        'rgba(148, 163, 184, 0.16)',
                                },

                                ticks: {
                                    color: '#64748b',

                                    callback(value) {
                                        return formatRate(
                                            value
                                        );
                                    },
                                },
                            },
                        },
                    },
                }
            );
        }

        function renderHistory(data) {
            const body =
                document.getElementById(
                    'currencyHistoryBody'
                );

            const rows = [
                ...data.history,
            ]
                .reverse()
                .slice(0, 15);

            if (rows.length === 0) {
                body.innerHTML = `
                    <tr>
                        <td
                            colspan="4"
                            class="text-center text-secondary"
                        >
                            Riwayat kurs belum tersedia.
                        </td>
                    </tr>
                `;

                return;
            }

            body.innerHTML = rows
                .map((point) => `
                    <tr>
                        <td>
                            ${escapeHtml(
                                formatDate(point.date)
                            )}
                        </td>

                        <td>
                            <span
                                class="badge text-bg-light"
                            >
                                ${escapeHtml(
                                    data.base_currency
                                )}
                                /
                                ${escapeHtml(
                                    data.target_currency
                                )}
                            </span>
                        </td>

                        <td class="currency-rate-number">
                            1
                            ${escapeHtml(
                                data.base_currency
                            )}
                            =
                            ${escapeHtml(
                                formatRate(point.rate)
                            )}
                            ${escapeHtml(
                                data.target_currency
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                point.provider
                                ?? data.provider
                            )}
                        </td>
                    </tr>
                `)
                .join('');
        }

        function renderDashboard(data) {
            currentData = data;

            document.getElementById(
                'latestCurrencyRate'
            ).textContent =
                `1 ${data.base_currency} = ${
                    formatRate(
                        data.latest_rate
                    )
                } ${data.target_currency}`;

            document.getElementById(
                'inverseCurrencyRate'
            ).textContent =
                `1 ${data.target_currency} = ${
                    formatRate(
                        data.inverse_rate
                    )
                } ${data.base_currency}`;

            document.getElementById(
                'latestCurrencyDate'
            ).textContent =
                `Data kurs ${
                    formatDate(
                        data.rate_date
                    )
                }`;

            document.getElementById(
                'providerUpdateDate'
            ).textContent =
                formatDateTime(
                    data.provider_updated_at
                );

            document.getElementById(
                'nextProviderUpdate'
            ).textContent =
                data.provider_next_update_at
                    ? `Update berikutnya: ${
                        formatDateTime(
                            data.provider_next_update_at
                        )
                    }`
                    : data.provider;

            document.getElementById(
                'currencyPairLabel'
            ).textContent =
                `${data.base_currency} / ${
                    data.target_currency
                }`;

            document.getElementById(
                'currencyChartDescription'
            ).textContent =
                `Tren ${
                    data.base_currency
                } terhadap ${
                    data.target_currency
                } selama ${
                    data.period_days
                } hari.`;

            document.getElementById(
                'highestCurrencyRate'
            ).textContent =
                `${formatRate(
                    data.highest_rate
                )} ${data.target_currency}`;

            document.getElementById(
                'lowestCurrencyRate'
            ).textContent =
                `${formatRate(
                    data.lowest_rate
                )} ${data.target_currency}`;

            document.getElementById(
                'averageCurrencyRate'
            ).textContent =
                `${formatRate(
                    data.average_rate
                )} ${data.target_currency}`;

            document.getElementById(
                'currencyPointCount'
            ).textContent =
                `${data.point_count} data`;

            renderTrend(data);
            renderChart(data);
            renderHistory(data);
            updateConversion();
        }

        async function loadCurrency(
            forceRefresh = false
        ) {
            hideError();
            setLoading(true);

            const params =
                new URLSearchParams({
                    base: baseCurrency.value,

                    target:
                        targetCurrency.value,

                    days:
                        currencyPeriod.value,
                });

            if (forceRefresh) {
                params.set('refresh', '1');
            }

            try {
                const response = await fetch(
                    `${currencyApiUrl}?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                const responseText =
                    await response.text();

                let result = null;

                if (
                    responseText.trim()
                    !== ''
                ) {
                    try {
                        result = JSON.parse(
                            responseText
                        );
                    } catch (error) {
                        throw new Error(
                            `Respons API bukan JSON. `
                            + `HTTP ${response.status}.`
                        );
                    }
                }

                if (! response.ok) {
                    throw new Error(
                        result?.detail
                        ?? result?.message
                        ?? `HTTP ${response.status}`
                    );
                }

                if (! result?.success) {
                    throw new Error(
                        result?.message
                        ?? 'Data kurs gagal dimuat.'
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

        showCurrencyButton.addEventListener(
            'click',
            () => loadCurrency(false)
        );

        refreshButton.addEventListener(
            'click',
            () => loadCurrency(true)
        );

        swapButton.addEventListener(
            'click',
            () => {
                const previousBase =
                    baseCurrency.value;

                baseCurrency.value =
                    targetCurrency.value;

                targetCurrency.value =
                    previousBase;

                loadCurrency(false);
            }
        );

        amountInput.addEventListener(
            'input',
            updateConversion
        );

        /*
         * Memperbarui tampilan setiap 15 menit.
         * Backend cache mencegah pemakaian kuota
         * API yang berlebihan.
         */
        window.setInterval(
            () => loadCurrency(false),
            15 * 60 * 1000
        );

        loadCurrency(false);
    </script>

    <div class="text-center text-secondary small mt-4">
    Data kurs terbaru oleh

    <a
        href="https://www.exchangerate-api.com"
        target="_blank"
        rel="noopener noreferrer"
    >
        ExchangeRate-API
    </a>.
</div>
@endpush