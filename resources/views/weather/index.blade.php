@extends('layouts.app')

@section('title', 'Global Weather - Global Supply Chain')

@push('styles')
    <style>
        /* =========================================
           WEATHER SUMMARY CARDS
           ========================================= */

        .weather-metric-card {
            position: relative;
            min-height: 174px;
            overflow: hidden;
        }

        .weather-metric-card::before {
            position: absolute;
            top: 0;
            left: 0;

            width: 100%;
            height: 4px;

            content: "";
            background-color:
                var(
                    --weather-card-accent,
                    #2563eb
                );
        }

        .weather-metric-card::after {
            position: absolute;
            top: -62px;
            right: -62px;

            width: 145px;
            height: 145px;

            content: "";
            border-radius: 50%;

            background-color:
                var(
                    --weather-card-soft,
                    rgba(37, 99, 235, 0.08)
                );
        }

        .weather-metric-card > * {
            position: relative;
            z-index: 1;
        }

        .weather-metric-temperature {
            --weather-card-accent: #14b8a6;
            --weather-card-soft:
                rgba(20, 184, 166, 0.1);
        }

        .weather-metric-humidity {
            --weather-card-accent: #3b82f6;
            --weather-card-soft:
                rgba(59, 130, 246, 0.09);
        }

        .weather-metric-wind {
            --weather-card-accent: #64748b;
            --weather-card-soft:
                rgba(100, 116, 139, 0.09);
        }

        .weather-metric-risk {
            --weather-card-accent: #f59e0b;
            --weather-card-soft:
                rgba(245, 158, 11, 0.1);
        }

        .weather-metric-header {
            display: flex;
            min-height: 24px;
            margin-bottom: 25px;

            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .weather-metric-label {
            display: block;
            max-width: 185px;

            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.5;
            letter-spacing: 0.055em;
            text-transform: uppercase;
        }

        .weather-metric-value {
            margin: 0 0 16px;

            color: #526581;
            font-size: clamp(
                2rem,
                3vw,
                2.65rem
            );
            font-weight: 750;
            line-height: 1;
            letter-spacing: -0.035em;
        }

        .weather-metric-description {
            margin: 0;

            color: #71829e;
            font-size: 13px;
            line-height: 1.5;
        }

        /* =========================================
           WEATHER CONDITION MAP
           ========================================= */

        #weatherMap {
            position: relative;

            width: 100%;
            height: 480px;

            overflow: hidden;

            border: 1px solid #dbe3ef;
            border-radius: 16px;

            background-color: #dbeafe;

            box-shadow:
                inset 0 0 0 1px
                rgba(255, 255, 255, 0.45);
        }

        .weather-map-marker-wrapper {
            border: 0;
            background: transparent;
        }

        .weather-map-marker {
            position: relative;

            display: flex;
            width: 82px;
            min-height: 82px;
            padding: 9px 7px;

            align-items: center;
            justify-content: center;
            flex-direction: column;

            color: #0f172a;

            border:
                3px solid
                var(
                    --weather-risk-color,
                    #2563eb
                );

            border-radius: 50%;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.97
                );

            box-shadow:
                0 12px 30px
                rgba(15, 23, 42, 0.28);

            animation:
                weatherMarkerPulse
                2.5s ease-in-out
                infinite;
        }

        .weather-map-marker::before {
            position: absolute;
            z-index: -1;
            inset: -10px;

            content: "";

            border:
                2px solid
                var(
                    --weather-risk-color,
                    #2563eb
                );

            border-radius: 50%;
            opacity: 0.22;
        }

        .weather-map-marker::after {
            position: absolute;
            bottom: -10px;
            left: 50%;

            width: 18px;
            height: 18px;

            content: "";

            border-right:
                3px solid
                var(
                    --weather-risk-color,
                    #2563eb
                );

            border-bottom:
                3px solid
                var(
                    --weather-risk-color,
                    #2563eb
                );

            background-color: #ffffff;

            transform:
                translateX(-50%)
                rotate(45deg);
        }

        .weather-map-marker-icon {
            display: flex;
            width: 25px;
            height: 25px;
            margin-bottom: 2px;

            align-items: center;
            justify-content: center;

            color: #2563eb;
        }

        .weather-map-marker-icon svg {
            width: 25px;
            height: 25px;
        }

        .weather-map-marker strong {
            display: block;

            color: #0f172a;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
        }

        .weather-clear {
            background:
                linear-gradient(
                    145deg,
                    #fff8d8,
                    #ffffff
                );
        }

        .weather-partly-cloudy {
            background:
                linear-gradient(
                    145deg,
                    #fef3c7,
                    #e0f2fe
                );
        }

        .weather-cloudy,
        .weather-fog {
            background:
                linear-gradient(
                    145deg,
                    #e2e8f0,
                    #ffffff
                );
        }

        .weather-drizzle,
        .weather-rain {
            background:
                linear-gradient(
                    145deg,
                    #bfdbfe,
                    #ffffff
                );
        }

        .weather-snow {
            background:
                linear-gradient(
                    145deg,
                    #cffafe,
                    #ffffff
                );
        }

        .weather-storm {
            background:
                linear-gradient(
                    145deg,
                    #ddd6fe,
                    #ffffff
                );
        }

        .weather-map-location-card {
            display: flex;
            padding: 12px 14px;
            margin-bottom: 14px;

            align-items: center;
            justify-content: space-between;
            gap: 16px;

            border: 1px solid #e2e8f0;
            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eff6ff
                );
        }

        .weather-map-location-card strong {
            display: block;
            margin-bottom: 3px;

            color: #0f172a;
            font-size: 13px;
        }

        .weather-map-location-card span {
            color: #64748b;
            font-size: 11px;
        }

        .weather-map-live {
            display: inline-flex;
            padding: 6px 9px;

            align-items: center;
            gap: 7px;

            color: #047857;
            border-radius: 999px;
            background-color: #d1fae5;

            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .weather-map-live::before {
            width: 7px;
            height: 7px;

            content: "";
            border-radius: 50%;
            background-color: #10b981;

            box-shadow:
                0 0 0 4px
                rgba(16, 185, 129, 0.15);
        }

        /* =========================================
           MAP LEGEND
           ========================================= */

        .weather-map-legend {
            min-width: 145px;
            padding: 12px 14px;

            color: #334155;

            border:
                1px solid
                rgba(148, 163, 184, 0.38);

            border-radius: 12px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.96
                );

            box-shadow:
                0 10px 28px
                rgba(15, 23, 42, 0.16);

            font-size: 11px;
            line-height: 1.5;
        }

        .weather-map-legend strong {
            display: block;
            margin-bottom: 8px;

            color: #0f172a;
            font-size: 11px;
            font-weight: 800;
        }

        .weather-map-legend div {
            display: flex;
            margin-top: 6px;

            align-items: center;
            gap: 8px;
        }

        .weather-legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;

            flex: 0 0 10px;
            border-radius: 50%;
        }

        .weather-legend-low {
            background-color: #16a34a;
        }

        .weather-legend-medium {
            background-color: #f59e0b;
        }

        .weather-legend-high {
            background-color: #dc2626;
        }

        /* =========================================
           MAP POPUP
           ========================================= */

        .weather-map-popup {
            min-width: 280px;

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                sans-serif;
        }

        .weather-map-popup-header {
            display: flex;
            padding-bottom: 10px;
            margin-bottom: 9px;

            align-items: center;
            gap: 10px;

            border-bottom:
                1px solid
                #e2e8f0;
        }

        .weather-map-popup-icon {
            display: flex;
            width: 44px;
            height: 44px;

            flex: 0 0 44px;
            align-items: center;
            justify-content: center;

            color: #2563eb;

            border-radius: 12px;
            background-color: #f1f5f9;
        }

        .weather-map-popup-icon svg {
            width: 27px;
            height: 27px;
        }

        .weather-map-popup-header strong {
            display: block;

            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.3;
        }

        .weather-map-popup-header span {
            display: block;
            margin-top: 2px;

            color: #64748b;
            font-size: 11px;
        }

        .weather-map-popup-condition {
            display: flex;
            padding: 7px 9px;
            margin-bottom: 10px;

            align-items: center;
            justify-content: space-between;
            gap: 10px;

            color: #1d4ed8;
            border-radius: 8px;
            background-color: #eff6ff;

            font-size: 11px;
            font-weight: 750;
        }

        .weather-map-popup-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );

            gap: 7px;
        }

        .weather-map-popup-grid div {
            min-width: 0;
            padding: 8px;

            border: 1px solid #e2e8f0;
            border-radius: 8px;

            background-color: #f8fafc;
        }

        .weather-map-popup-grid span {
            display: block;
            margin-bottom: 3px;

            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .weather-map-popup-grid strong {
            display: block;

            overflow: hidden;

            color: #0f172a;
            font-size: 11px;
            font-weight: 750;
            line-height: 1.35;
            text-overflow: ellipsis;
        }

        .weather-map-popup-risk {
            grid-column: 1 / -1;
        }

        /* =========================================
           TABLE WEATHER ICON
           ========================================= */

        .weather-condition-cell {
            display: inline-flex;

            align-items: center;
            gap: 9px;
        }

        .weather-condition-table-icon {
            display: inline-flex;
            width: 30px;
            height: 30px;

            flex: 0 0 30px;
            align-items: center;
            justify-content: center;

            color: #2563eb;

            border: 1px solid #dbe5f1;
            border-radius: 8px;

            background-color: #f8fafc;
        }

        .weather-condition-table-icon svg {
            width: 18px;
            height: 18px;
        }

        @keyframes weatherMarkerPulse {
            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.045);
            }
        }

        @media (max-width: 767px) {
            #weatherMap {
                height: 400px;
            }
        }

        @media (max-width: 575px) {
            #weatherMap {
                height: 360px;
            }

            .weather-map-legend {
                display: none;
            }

            .weather-map-popup {
                min-width: 220px;
            }

            .weather-map-popup-grid {
                grid-template-columns: 1fr;
            }

            .weather-map-location-card {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="topbar">
        <div>
            <span class="page-eyebrow">
                Weather Intelligence
            </span>

            <h2>Global Weather Monitoring</h2>

            <p>
                Monitoring kondisi cuaca dan potensi
                risiko berdasarkan negara yang dipilih.
            </p>
        </div>

        <div>
            <span
                id="weatherStatusBadge"
                class="badge text-bg-primary fs-6"
            >
                {{ $totalCountries }} Countries
            </span>
        </div>
    </div>

    @if ($countries->isEmpty())
        <div class="alert alert-warning">
            Data negara dengan latitude dan longitude
            belum tersedia.
        </div>
    @else
        <section class="dashboard-card mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label
                        for="weatherCountry"
                        class="form-label"
                    >
                        Pilih Negara
                    </label>

                    <select
                        id="weatherCountry"
                        class="form-select"
                    >
                        @foreach ($countries as $country)
                            <option
                                value="{{ $country->id }}"
                                @selected(
                                    $country->id ===
                                    $defaultCountryId
                                )
                            >
                                {{ $country->name }}

                                @if ($country->capital)
                                    — {{ $country->capital }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button
                        type="button"
                        id="reloadWeather"
                        class="btn btn-primary w-100"
                    >
                        Reload Weather
                    </button>
                </div>
            </div>
        </section>

        <div
            id="weatherLoading"
            class="alert alert-info"
        >
            Mengambil data cuaca...
        </div>

        <div
            id="weatherError"
            class="alert alert-danger d-none"
        ></div>

        <section class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div
                    class="
                        dashboard-card
                        weather-metric-card
                        weather-metric-temperature
                    "
                >
                    <div class="weather-metric-header">
                        <span class="weather-metric-label">
                            Current Temperature
                        </span>
                    </div>

                    <p
                        id="weatherTemperature"
                        class="weather-metric-value"
                    >
                        -
                    </p>

                    <p
                        id="weatherCondition"
                        class="weather-metric-description"
                    >
                        Menunggu data
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div
                    class="
                        dashboard-card
                        weather-metric-card
                        weather-metric-humidity
                    "
                >
                    <div class="weather-metric-header">
                        <span class="weather-metric-label">
                            Humidity
                        </span>
                    </div>

                    <p
                        id="weatherHumidity"
                        class="weather-metric-value"
                    >
                        -
                    </p>

                    <p class="weather-metric-description">
                        Kelembapan udara saat ini
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div
                    class="
                        dashboard-card
                        weather-metric-card
                        weather-metric-wind
                    "
                >
                    <div class="weather-metric-header">
                        <span class="weather-metric-label">
                            Wind Speed
                        </span>
                    </div>

                    <p
                        id="weatherWind"
                        class="weather-metric-value"
                    >
                        -
                    </p>

                    <p
                        id="weatherWindGust"
                        class="weather-metric-description"
                    >
                        Hembusan: -
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div
                    class="
                        dashboard-card
                        weather-metric-card
                        weather-metric-risk
                    "
                >
                    <div class="weather-metric-header">
                        <span class="weather-metric-label">
                            Weather Risk
                        </span>
                    </div>

                    <p
                        id="weatherRisk"
                        class="weather-metric-value"
                    >
                        -
                    </p>

                    <p
                        id="weatherRiskLabel"
                        class="weather-metric-description"
                    >
                        Menunggu perhitungan
                    </p>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="dashboard-card">
                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-start
                            flex-wrap
                            gap-3
                            mb-3
                        "
                    >
                        <div>
                            <span class="section-eyebrow">
                                Live Conditions
                            </span>

                            <h3 class="mb-1">
                                Weather Condition Map
                            </h3>

                            <p class="text-secondary mb-0">
                                Kondisi cuaca pada titik
                                lokasi negara yang dipilih.
                            </p>
                        </div>

                        <div
                            id="weatherTimezone"
                            class="small text-secondary"
                        >
                            Timezone: -
                        </div>
                    </div>

                    <div class="weather-map-location-card">
                        <div>
                            <strong id="weatherLocationName">
                                Lokasi belum dipilih
                            </strong>

                            <span id="weatherLocation">
                                Koordinat akan ditampilkan.
                            </span>
                        </div>

                        <span class="weather-map-live">
                            Live Weather
                        </span>
                    </div>

                    <div id="weatherMap"></div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="dashboard-card h-100">
                    <span class="section-eyebrow">
                        Risk Analysis
                    </span>

                    <h3 class="mb-1">
                        Risk Indicators
                    </h3>

                    <p class="text-secondary">
                        Faktor cuaca yang memengaruhi
                        skor risiko.
                    </p>

                    <ul
                        id="weatherRiskFactors"
                        class="risk-factor-list"
                    >
                        <li>Menunggu data cuaca.</li>
                    </ul>

                    <hr>

                    <div class="weather-detail-grid">
                        <div>
                            <span>Feels Like</span>

                            <strong id="weatherFeelsLike">
                                -
                            </strong>
                        </div>

                        <div>
                            <span>Precipitation</span>

                            <strong id="weatherPrecipitation">
                                -
                            </strong>
                        </div>

                        <div>
                            <span>Cloud Cover</span>

                            <strong id="weatherCloudCover">
                                -
                            </strong>
                        </div>

                        <div>
                            <span>Last Updated</span>

                            <strong id="weatherUpdatedAt">
                                -
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-card mb-4">
            <div class="chart-card-header">
                <div>
                    <span class="section-eyebrow">
                        Forecast Analytics
                    </span>

                    <h3>
                        7-Day Weather Forecast
                    </h3>

                    <p>
                        Grafik suhu maksimum, suhu minimum,
                        dan curah hujan selama tujuh hari.
                    </p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="weatherForecastChart"></canvas>
            </div>
        </section>

        <section class="dashboard-card">
            <span class="section-eyebrow">
                Forecast Details
            </span>

            <h3 class="mb-1">
                Forecast Details
            </h3>

            <p class="text-secondary">
                Ringkasan cuaca untuk tujuh hari ke depan.
            </p>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kondisi</th>
                            <th>Suhu</th>
                            <th>Curah Hujan</th>
                            <th>Peluang Hujan</th>
                            <th>Angin Maks.</th>
                        </tr>
                    </thead>

                    <tbody id="weatherForecastTable">
                        <tr>
                            <td
                                colspan="6"
                                class="
                                    text-center
                                    text-secondary
                                "
                            >
                                Menunggu data cuaca.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection

@if ($countries->isNotEmpty())
    @push('scripts')
        <script type="module">
            const weatherApiUrl =
                @json(route('api.weather.index'));

            const countrySelect =
                document.getElementById(
                    'weatherCountry'
                );

            const reloadButton =
                document.getElementById(
                    'reloadWeather'
                );

            const loadingBox =
                document.getElementById(
                    'weatherLoading'
                );

            const errorBox =
                document.getElementById(
                    'weatherError'
                );

            const riskElement =
                document.getElementById(
                    'weatherRisk'
                );

            const statusBadge =
                document.getElementById(
                    'weatherStatusBadge'
                );

            const forecastTable =
                document.getElementById(
                    'weatherForecastTable'
                );

            const riskFactors =
                document.getElementById(
                    'weatherRiskFactors'
                );

            let weatherMap = null;
            let weatherMarker = null;
            let weatherRiskCircle = null;
            let weatherLegend = null;
            let forecastChart = null;

            function formatNumber(
                value,
                decimals = 1
            ) {
                if (
                    value === null
                    || value === undefined
                    || value === ''
                    || Number.isNaN(Number(value))
                ) {
                    return '-';
                }

                return new Intl.NumberFormat(
                    'id-ID',
                    {
                        minimumFractionDigits:
                            decimals,

                        maximumFractionDigits:
                            decimals,
                    }
                ).format(
                    Number(value)
                );
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
                        weekday: 'short',
                        day: '2-digit',
                        month: 'short',
                    }
                );
            }

            function formatDateTime(value) {
                if (! value) {
                    return '-';
                }

                const date =
                    new Date(value);

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

            function weatherIconSvg(type) {
                const icons = {
                    clear: `
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="4"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />

                            <path
                                d="
                                    M12 2.5V5
                                    M12 19V21.5
                                    M2.5 12H5
                                    M19 12H21.5
                                    M5.3 5.3L7.1 7.1
                                    M16.9 16.9L18.7 18.7
                                    M18.7 5.3L16.9 7.1
                                    M7.1 16.9L5.3 18.7
                                "
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    `,

                    cloudy: `
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="
                                    M7.2 18H17.3
                                    C19.3 18 21 16.4 21 14.4
                                    C21 12.5 19.5 10.9 17.6 10.8
                                    C17.1 8.4 15 6.7 12.5 6.7
                                    C9.8 6.7 7.6 8.6 7.2 11.2
                                    C4.8 11.3 3 12.8 3 14.8
                                    C3 16.6 4.5 18 6.3 18
                                    H7.2
                                "
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    `,

                    rain: `
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="
                                    M7 15.5H17
                                    C19.2 15.5 21 13.8 21 11.7
                                    C21 9.6 19.4 8 17.4 7.9
                                    C16.8 5.6 14.7 4 12.2 4
                                    C9.4 4 7.1 6 6.7 8.6
                                    C4.6 8.8 3 10.3 3 12.2
                                    C3 14 4.5 15.5 6.3 15.5
                                    H7
                                "
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="
                                    M8 18L7.4 20
                                    M12.5 18L11.9 20
                                    M17 18L16.4 20
                                "
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    `,

                    storm: `
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="
                                    M7 14H17
                                    C19.2 14 21 12.3 21 10.2
                                    C21 8.1 19.4 6.5 17.4 6.4
                                    C16.8 4.1 14.7 2.5 12.2 2.5
                                    C9.4 2.5 7.1 4.5 6.7 7.1
                                    C4.6 7.3 3 8.8 3 10.7
                                    C3 12.5 4.5 14 6.3 14
                                    H7
                                "
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M13 14L9.8 18H12L10.8 21.5L15.5 16.5H13.1L14.5 14"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    `,

                    snow: `
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="
                                    M7 14.5H17
                                    C19.2 14.5 21 12.8 21 10.7
                                    C21 8.6 19.4 7 17.4 6.9
                                    C16.8 4.6 14.7 3 12.2 3
                                    C9.4 3 7.1 5 6.7 7.6
                                    C4.6 7.8 3 9.3 3 11.2
                                    C3 13 4.5 14.5 6.3 14.5
                                    H7
                                "
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="
                                    M8 18H8.01
                                    M12 20H12.01
                                    M16 18H16.01
                                "
                                stroke="currentColor"
                                stroke-width="2.4"
                                stroke-linecap="round"
                            />
                        </svg>
                    `,

                    fog: `
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M5 8H19M3 12H17M6 16H21"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    `,
                };

                return icons[type]
                    ?? icons.cloudy;
            }

            function getWeatherVisual(
                weatherCode,
                description = ''
            ) {
                const code =
                    Number(weatherCode);

                if (code === 0) {
                    return {
                        type: 'clear',
                        className:
                            'weather-clear',
                        label: 'Cerah',
                    };
                }

                if (
                    [1, 2].includes(code)
                ) {
                    return {
                        type: 'cloudy',
                        className:
                            'weather-partly-cloudy',
                        label:
                            'Berawan sebagian',
                    };
                }

                if (code === 3) {
                    return {
                        type: 'cloudy',
                        className:
                            'weather-cloudy',
                        label: 'Berawan',
                    };
                }

                if (
                    [45, 48].includes(code)
                ) {
                    return {
                        type: 'fog',
                        className:
                            'weather-fog',
                        label: 'Berkabut',
                    };
                }

                if (
                    [
                        51,
                        53,
                        55,
                        56,
                        57,
                    ].includes(code)
                ) {
                    return {
                        type: 'rain',
                        className:
                            'weather-drizzle',
                        label: 'Gerimis',
                    };
                }

                if (
                    [
                        61,
                        63,
                        65,
                        66,
                        67,
                        80,
                        81,
                        82,
                    ].includes(code)
                ) {
                    return {
                        type: 'rain',
                        className:
                            'weather-rain',
                        label: 'Hujan',
                    };
                }

                if (
                    [
                        71,
                        73,
                        75,
                        77,
                        85,
                        86,
                    ].includes(code)
                ) {
                    return {
                        type: 'snow',
                        className:
                            'weather-snow',
                        label: 'Salju',
                    };
                }

                if (
                    [
                        95,
                        96,
                        99,
                    ].includes(code)
                ) {
                    return {
                        type: 'storm',
                        className:
                            'weather-storm',
                        label:
                            'Badai petir',
                    };
                }

                const normalized =
                    String(description)
                        .toLowerCase();

                if (
                    normalized.includes(
                        'thunder'
                    )
                    || normalized.includes(
                        'storm'
                    )
                    || normalized.includes(
                        'badai'
                    )
                ) {
                    return {
                        type: 'storm',
                        className:
                            'weather-storm',
                        label:
                            description
                            || 'Badai',
                    };
                }

                if (
                    normalized.includes(
                        'rain'
                    )
                    || normalized.includes(
                        'hujan'
                    )
                ) {
                    return {
                        type: 'rain',
                        className:
                            'weather-rain',
                        label:
                            description
                            || 'Hujan',
                    };
                }

                if (
                    normalized.includes(
                        'fog'
                    )
                    || normalized.includes(
                        'kabut'
                    )
                ) {
                    return {
                        type: 'fog',
                        className:
                            'weather-fog',
                        label:
                            description
                            || 'Berkabut',
                    };
                }

                return {
                    type: 'cloudy',
                    className:
                        'weather-cloudy',
                    label:
                        description
                        || 'Kondisi cuaca',
                };
            }

            function getRiskVisual(level) {
                if (level === 'high') {
                    return {
                        color: '#dc2626',
                        radius: 170000,
                        label:
                            'Risiko Tinggi',
                    };
                }

                if (level === 'medium') {
                    return {
                        color: '#f59e0b',
                        radius: 125000,
                        label:
                            'Risiko Sedang',
                    };
                }

                return {
                    color: '#16a34a',
                    radius: 85000,
                    label:
                        'Risiko Rendah',
                };
            }

            function setLoading(isLoading) {
                loadingBox.classList.toggle(
                    'd-none',
                    ! isLoading
                );

                countrySelect.disabled =
                    isLoading;

                reloadButton.disabled =
                    isLoading;

                reloadButton.textContent =
                    isLoading
                        ? 'Loading...'
                        : 'Reload Weather';
            }

            function showError(message) {
                errorBox.textContent =
                    message;

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

            function updateRiskAppearance(
                risk
            ) {
                const level =
                    risk?.level
                    ?? 'low';

                riskElement.classList.remove(
                    'status-low',
                    'status-medium',
                    'status-high'
                );

                riskElement.classList.add(
                    `status-${level}`
                );

                statusBadge.classList.remove(
                    'text-bg-primary',
                    'text-bg-success',
                    'text-bg-warning',
                    'text-bg-danger'
                );

                if (level === 'low') {
                    statusBadge.classList.add(
                        'text-bg-success'
                    );
                } else if (
                    level === 'medium'
                ) {
                    statusBadge.classList.add(
                        'text-bg-warning'
                    );
                } else {
                    statusBadge.classList.add(
                        'text-bg-danger'
                    );
                }

                statusBadge.textContent =
                    risk?.label
                    ?? 'Weather Risk';
            }

            function renderRiskFactors(
                factors
            ) {
                riskFactors.innerHTML = '';

                if (
                    ! Array.isArray(factors)
                    || factors.length === 0
                ) {
                    const item =
                        document.createElement(
                            'li'
                        );

                    item.textContent =
                        'Tidak ada faktor risiko cuaca yang signifikan.';

                    riskFactors.appendChild(
                        item
                    );

                    return;
                }

                factors.forEach(
                    (factor) => {
                        const item =
                            document.createElement(
                                'li'
                            );

                        item.textContent =
                            factor;

                        riskFactors.appendChild(
                            item
                        );
                    }
                );
            }

            function createMapLegend() {
                weatherLegend =
                    window.L.control({
                        position:
                            'bottomright',
                    });

                weatherLegend.onAdd =
                    function () {
                        const legend =
                            window.L.DomUtil
                                .create(
                                    'div',
                                    'weather-map-legend'
                                );

                        legend.innerHTML = `
                            <strong>
                                Tingkat Risiko
                            </strong>

                            <div>
                                <span
                                    class="
                                        weather-legend-dot
                                        weather-legend-low
                                    "
                                ></span>

                                Risiko Rendah
                            </div>

                            <div>
                                <span
                                    class="
                                        weather-legend-dot
                                        weather-legend-medium
                                    "
                                ></span>

                                Risiko Sedang
                            </div>

                            <div>
                                <span
                                    class="
                                        weather-legend-dot
                                        weather-legend-high
                                    "
                                ></span>

                                Risiko Tinggi
                            </div>
                        `;

                        return legend;
                    };

                weatherLegend.addTo(
                    weatherMap
                );
            }

            function renderMap(data) {
                const country =
                    data.country ?? {};

                const current =
                    data.current ?? {};

                const risk =
                    data.risk ?? {
                        level: 'low',
                        score: 0,
                        label:
                            'Risiko Rendah',
                    };

                const latitude =
                    Number(
                        country.latitude
                    );

                const longitude =
                    Number(
                        country.longitude
                    );

                if (
                    Number.isNaN(latitude)
                    || Number.isNaN(longitude)
                ) {
                    return;
                }

                const weatherVisual =
                    getWeatherVisual(
                        current.weather_code
                        ?? current.code
                        ?? null,

                        current.description
                    );

                const riskVisual =
                    getRiskVisual(
                        risk.level
                    );

                if (! weatherMap) {
                    weatherMap =
                        window.L
                            .map(
                                'weatherMap',
                                {
                                    worldCopyJump:
                                        true,

                                    zoomControl:
                                        true,
                                }
                            )
                            .setView(
                                [
                                    latitude,
                                    longitude,
                                ],
                                5
                            );

                    window.L.tileLayer(
                        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        {
                            maxZoom: 19,

                            attribution:
                                '&copy; OpenStreetMap contributors',
                        }
                    ).addTo(
                        weatherMap
                    );

                    createMapLegend();
                }

                weatherMap.setView(
                    [
                        latitude,
                        longitude,
                    ],
                    5,
                    {
                        animate: true,
                    }
                );

                if (weatherMarker) {
                    weatherMap.removeLayer(
                        weatherMarker
                    );
                }

                if (weatherRiskCircle) {
                    weatherMap.removeLayer(
                        weatherRiskCircle
                    );
                }

                weatherRiskCircle =
                    window.L.circle(
                        [
                            latitude,
                            longitude,
                        ],
                        {
                            radius:
                                riskVisual.radius,

                            color:
                                riskVisual.color,

                            fillColor:
                                riskVisual.color,

                            fillOpacity:
                                0.12,

                            opacity:
                                0.78,

                            weight: 2,

                            dashArray:
                                '8 6',
                        }
                    )
                    .addTo(weatherMap);

                const markerIcon =
                    window.L.divIcon({
                        className:
                            'weather-map-marker-wrapper',

                        html: `
                            <div
                                class="
                                    weather-map-marker
                                    ${weatherVisual.className}
                                "
                                style="
                                    --weather-risk-color:
                                        ${riskVisual.color};
                                "
                            >
                                <span
                                    class="
                                        weather-map-marker-icon
                                    "
                                >
                                    ${weatherIconSvg(
                                        weatherVisual.type
                                    )}
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.temperature
                                    )}°C
                                </strong>
                            </div>
                        `,

                        iconSize:
                            [88, 92],

                        iconAnchor:
                            [44, 50],

                        popupAnchor:
                            [0, -46],
                    });

                const popupContent = `
                    <div class="weather-map-popup">
                        <div class="weather-map-popup-header">
                            <span class="weather-map-popup-icon">
                                ${weatherIconSvg(
                                    weatherVisual.type
                                )}
                            </span>

                            <div>
                                <strong>
                                    ${escapeHtml(
                                        country.name
                                        ?? 'Negara'
                                    )}
                                </strong>

                                <span>
                                    ${escapeHtml(
                                        country.capital
                                        ?? 'Lokasi negara'
                                    )}
                                </span>
                            </div>
                        </div>

                        <div class="weather-map-popup-condition">
                            <span>
                                ${escapeHtml(
                                    current.description
                                    ?? weatherVisual.label
                                )}
                            </span>

                            <strong>
                                ${formatNumber(
                                    current.temperature
                                )}°C
                            </strong>
                        </div>

                        <div class="weather-map-popup-grid">
                            <div>
                                <span>
                                    Terasa
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.apparent_temperature
                                    )} °C
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Kelembapan
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.humidity,
                                        0
                                    )}%
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Curah Hujan
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.precipitation
                                    )} mm
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Awan
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.cloud_cover,
                                        0
                                    )}%
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Angin
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.wind_speed
                                    )} km/h
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Hembusan
                                </span>

                                <strong>
                                    ${formatNumber(
                                        current.wind_gusts
                                    )} km/h
                                </strong>
                            </div>

                            <div class="weather-map-popup-risk">
                                <span>
                                    Weather Risk
                                </span>

                                <strong
                                    style="
                                        color:
                                            ${riskVisual.color};
                                    "
                                >
                                    ${escapeHtml(
                                        risk.label
                                        ?? riskVisual.label
                                    )}

                                    — ${formatNumber(
                                        risk.score,
                                        0
                                    )}/100
                                </strong>
                            </div>
                        </div>
                    </div>
                `;

                weatherMarker =
                    window.L.marker(
                        [
                            latitude,
                            longitude,
                        ],
                        {
                            icon:
                                markerIcon,

                            title:
                                `${country.name ?? ''}`
                                + ' - '
                                + `${
                                    current.description
                                    ?? weatherVisual.label
                                }`,
                        }
                    )
                    .addTo(weatherMap)
                    .bindPopup(
                        popupContent,
                        {
                            maxWidth: 340,
                            minWidth: 280,
                        }
                    )
                    .openPopup();

                weatherRiskCircle.bringToBack();

                window.setTimeout(
                    () => {
                        weatherMap
                            .invalidateSize();
                    },
                    150
                );
            }

            function renderChart(forecast) {
                const chartElement =
                    document.getElementById(
                        'weatherForecastChart'
                    );

                if (! chartElement) {
                    return;
                }

                if (forecastChart) {
                    forecastChart.destroy();
                }

                forecastChart =
                    new window.Chart(
                        chartElement,
                        {
                            data: {
                                labels:
                                    forecast.map(
                                        (day) =>
                                            formatDate(
                                                day.date
                                            )
                                    ),

                                datasets: [
                                    {
                                        type:
                                            'line',

                                        label:
                                            'Maximum Temperature (°C)',

                                        data:
                                            forecast.map(
                                                (day) =>
                                                    day.temperature_max
                                            ),

                                        borderColor:
                                            '#dc2626',

                                        backgroundColor:
                                            'rgba(220, 38, 38, 0.12)',

                                        pointBackgroundColor:
                                            '#dc2626',

                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        borderWidth: 2,
                                        tension: 0.35,
                                        fill: false,

                                        yAxisID:
                                            'temperature',
                                    },

                                    {
                                        type:
                                            'line',

                                        label:
                                            'Minimum Temperature (°C)',

                                        data:
                                            forecast.map(
                                                (day) =>
                                                    day.temperature_min
                                            ),

                                        borderColor:
                                            '#2563eb',

                                        backgroundColor:
                                            'rgba(37, 99, 235, 0.12)',

                                        pointBackgroundColor:
                                            '#2563eb',

                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        borderWidth: 2,
                                        tension: 0.35,
                                        fill: false,

                                        yAxisID:
                                            'temperature',
                                    },

                                    {
                                        type:
                                            'bar',

                                        label:
                                            'Precipitation (mm)',

                                        data:
                                            forecast.map(
                                                (day) =>
                                                    day.precipitation_sum
                                            ),

                                        backgroundColor:
                                            'rgba(14, 165, 233, 0.28)',

                                        borderColor:
                                            '#0ea5e9',

                                        borderWidth: 1,
                                        borderRadius: 5,

                                        yAxisID:
                                            'precipitation',
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

                                plugins: {
                                    legend: {
                                        position: 'top',

                                        labels: {
                                            usePointStyle:
                                                true,

                                            boxWidth: 8,
                                            padding: 18,
                                        },
                                    },

                                    tooltip: {
                                        padding: 12,
                                        cornerRadius: 8,
                                    },
                                },

                                scales: {
                                    x: {
                                        grid: {
                                            display: false,
                                        },
                                    },

                                    temperature: {
                                        type: 'linear',

                                        position: 'left',

                                        grid: {
                                            color:
                                                'rgba(148, 163, 184, 0.15)',
                                        },

                                        title: {
                                            display: true,

                                            text:
                                                'Temperature (°C)',
                                        },
                                    },

                                    precipitation: {
                                        type: 'linear',

                                        position: 'right',

                                        beginAtZero: true,

                                        grid: {
                                            drawOnChartArea:
                                                false,
                                        },

                                        title: {
                                            display: true,

                                            text:
                                                'Precipitation (mm)',
                                        },
                                    },
                                },
                            },
                        }
                    );
            }

            function renderForecastTable(
                forecast
            ) {
                if (
                    ! Array.isArray(forecast)
                    || forecast.length === 0
                ) {
                    forecastTable.innerHTML = `
                        <tr>
                            <td
                                colspan="6"
                                class="
                                    text-center
                                    text-secondary
                                "
                            >
                                Forecast tidak tersedia.
                            </td>
                        </tr>
                    `;

                    return;
                }

                forecastTable.innerHTML =
                    forecast.map(
                        (day) => {
                            const visual =
                                getWeatherVisual(
                                    day.weather_code
                                    ?? day.code
                                    ?? null,

                                    day.description
                                );

                            return `
                                <tr>
                                    <td>
                                        ${escapeHtml(
                                            formatDate(
                                                day.date
                                            )
                                        )}
                                    </td>

                                    <td>
                                        <span
                                            class="
                                                weather-condition-cell
                                            "
                                        >
                                            <span
                                                class="
                                                    weather-condition-table-icon
                                                "
                                            >
                                                ${weatherIconSvg(
                                                    visual.type
                                                )}
                                            </span>

                                            <span>
                                                ${escapeHtml(
                                                    day.description
                                                    ?? visual.label
                                                )}
                                            </span>
                                        </span>
                                    </td>

                                    <td>
                                        ${formatNumber(
                                            day.temperature_min
                                        )}°C

                                        –

                                        ${formatNumber(
                                            day.temperature_max
                                        )}°C
                                    </td>

                                    <td>
                                        ${formatNumber(
                                            day.precipitation_sum
                                        )} mm
                                    </td>

                                    <td>
                                        ${formatNumber(
                                            day.precipitation_probability,
                                            0
                                        )}%
                                    </td>

                                    <td>
                                        ${formatNumber(
                                            day.wind_speed_max
                                        )} km/h
                                    </td>
                                </tr>
                            `;
                        }
                    ).join('');
            }

            function renderWeather(data) {
                const country =
                    data.country ?? {};

                const current =
                    data.current ?? {};

                const risk =
                    data.risk ?? {
                        score: 0,
                        level: 'low',
                        label:
                            'Risiko Rendah',
                        factors: [],
                    };

                const forecast =
                    Array.isArray(
                        data.forecast
                    )
                        ? data.forecast
                        : [];

                const weatherVisual =
                    getWeatherVisual(
                        current.weather_code
                        ?? current.code
                        ?? null,

                        current.description
                    );

                document.getElementById(
                    'weatherTemperature'
                ).textContent =
                    `${formatNumber(
                        current.temperature
                    )} °C`;

                document.getElementById(
                    'weatherCondition'
                ).textContent =
                    current.description
                    ?? weatherVisual.label;

                document.getElementById(
                    'weatherHumidity'
                ).textContent =
                    `${formatNumber(
                        current.humidity,
                        0
                    )}%`;

                document.getElementById(
                    'weatherWind'
                ).textContent =
                    `${formatNumber(
                        current.wind_speed
                    )} km/h`;

                document.getElementById(
                    'weatherWindGust'
                ).textContent =
                    `Hembusan: ${
                        formatNumber(
                            current.wind_gusts
                        )
                    } km/h`;

                riskElement.textContent =
                    `${formatNumber(
                        risk.score,
                        0
                    )}/100`;

                document.getElementById(
                    'weatherRiskLabel'
                ).textContent =
                    risk.label
                    ?? 'Weather Risk';

                document.getElementById(
                    'weatherFeelsLike'
                ).textContent =
                    `${formatNumber(
                        current.apparent_temperature
                    )} °C`;

                document.getElementById(
                    'weatherPrecipitation'
                ).textContent =
                    `${formatNumber(
                        current.precipitation
                    )} mm`;

                document.getElementById(
                    'weatherCloudCover'
                ).textContent =
                    `${formatNumber(
                        current.cloud_cover,
                        0
                    )}%`;

                document.getElementById(
                    'weatherUpdatedAt'
                ).textContent =
                    formatDateTime(
                        current.time
                    );

                document.getElementById(
                    'weatherLocationName'
                ).textContent =
                    `${country.name ?? 'Negara'}`
                    + (
                        country.capital
                            ? ` — ${country.capital}`
                            : ''
                    );

                document.getElementById(
                    'weatherLocation'
                ).textContent =
                    `Koordinat: ${
                        country.latitude
                    }, ${
                        country.longitude
                    }`;

                document.getElementById(
                    'weatherTimezone'
                ).textContent =
                    `Timezone: ${
                        data.timezone
                        ?? '-'
                    }`;

                updateRiskAppearance(
                    risk
                );

                renderRiskFactors(
                    risk.factors
                );

                renderMap(data);

                renderChart(
                    forecast
                );

                renderForecastTable(
                    forecast
                );
            }

            async function loadWeather() {
                hideError();
                setLoading(true);

                try {
                    const params =
                        new URLSearchParams({
                            country_id:
                                countrySelect.value,
                        });

                    const response =
                        await fetch(
                            `${weatherApiUrl}?${params.toString()}`,
                            {
                                headers: {
                                    Accept:
                                        'application/json',
                                },
                            }
                        );

                    let result = null;

                    try {
                        result =
                            await response.json();
                    } catch {
                        throw new Error(
                            'Respons server bukan JSON yang valid.'
                        );
                    }

                    if (
                        ! response.ok
                        || ! result.success
                    ) {
                        throw new Error(
                            result.message
                            ?? 'Data cuaca gagal dimuat.'
                        );
                    }

                    renderWeather(
                        result.data
                    );
                } catch (error) {
                    showError(
                        error.message
                        ?? 'Terjadi kesalahan saat memuat cuaca.'
                    );
                } finally {
                    setLoading(false);
                }
            }

            countrySelect.addEventListener(
                'change',
                loadWeather
            );

            reloadButton.addEventListener(
                'click',
                loadWeather
            );

            loadWeather();
        </script>
    @endpush
@endif