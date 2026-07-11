@extends('layouts.app')

@section('title', 'Global Weather - Global Supply Chain')

@section('content')
    <div class="topbar">
        <div>
            <h2>Global Weather Monitoring</h2>

            <p>
                Monitoring kondisi cuaca dan potensi risiko
                berdasarkan negara yang dipilih.
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
                <div class="dashboard-card">
                    <h3>Current Temperature</h3>

                    <p
                        id="weatherTemperature"
                        class="metric-value"
                    >
                        -
                    </p>

                    <p
                        id="weatherCondition"
                        class="metric-description"
                    >
                        Menunggu data
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card">
                    <h3>Humidity</h3>

                    <p
                        id="weatherHumidity"
                        class="metric-value"
                    >
                        -
                    </p>

                    <p class="metric-description">
                        Kelembapan udara saat ini
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card">
                    <h3>Wind Speed</h3>

                    <p
                        id="weatherWind"
                        class="metric-value"
                    >
                        -
                    </p>

                    <p
                        id="weatherWindGust"
                        class="metric-description"
                    >
                        Hembusan: -
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card">
                    <h3>Weather Risk</h3>

                    <p
                        id="weatherRisk"
                        class="metric-value"
                    >
                        -
                    </p>

                    <p
                        id="weatherRiskLabel"
                        class="metric-description"
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
                        class="d-flex justify-content-between
                        gap-3 mb-3"
                    >
                        <div>
                            <h3 class="mb-1">
                                Weather Location Map
                            </h3>

                            <p
                                id="weatherLocation"
                                class="text-secondary mb-0"
                            >
                                Lokasi negara akan tampil
                                pada peta.
                            </p>
                        </div>

                        <div
                            id="weatherTimezone"
                            class="small text-secondary"
                        >
                            Timezone: -
                        </div>
                    </div>

                    <div id="weatherMap"></div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="dashboard-card">
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
            <h3 class="mb-1">
                7-Day Weather Forecast
            </h3>

            <p class="text-secondary">
                Grafik suhu maksimum, suhu minimum,
                dan curah hujan.
            </p>

            <div class="chart-container">
                <canvas id="weatherForecastChart"></canvas>
            </div>
        </section>

        <section class="dashboard-card">
            <h3 class="mb-1">
                Forecast Details
            </h3>

            <p class="text-secondary">
                Ringkasan cuaca untuk tujuh hari
                ke depan.
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
                                class="text-center
                                text-secondary"
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
            let forecastChart = null;

            function formatNumber(
                value,
                decimals = 1
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

                return new Date(value)
                    .toLocaleString(
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

            function updateRiskAppearance(
                risk
            ) {
                riskElement.classList.remove(
                    'status-low',
                    'status-medium',
                    'status-high'
                );

                riskElement.classList.add(
                    `status-${risk.level}`
                );

                statusBadge.classList.remove(
                    'text-bg-primary',
                    'text-bg-success',
                    'text-bg-warning',
                    'text-bg-danger'
                );

                if (risk.level === 'low') {
                    statusBadge.classList.add(
                        'text-bg-success'
                    );
                } else if (
                    risk.level === 'medium'
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
                    risk.label;
            }

            function renderRiskFactors(
                factors
            ) {
                riskFactors.innerHTML = '';

                factors.forEach((factor) => {
                    const item =
                        document.createElement('li');

                    item.textContent = factor;

                    riskFactors.appendChild(item);
                });
            }

            function renderMap(country) {
                const latitude =
                    Number(country.latitude);

                const longitude =
                    Number(country.longitude);

                if (! weatherMap) {
                    weatherMap = window.L
                        .map('weatherMap', {
                            worldCopyJump: true,
                        })
                        .setView(
                            [latitude, longitude],
                            4
                        );

                    window.L.tileLayer(
                        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        {
                            maxZoom: 19,

                            attribution:
                                '&copy; OpenStreetMap contributors',
                        }
                    ).addTo(weatherMap);
                }

                weatherMap.setView(
                    [latitude, longitude],
                    4,
                    {
                        animate: true,
                    }
                );

                if (weatherMarker) {
                    weatherMap.removeLayer(
                        weatherMarker
                    );
                }

                weatherMarker = window.L
                    .circleMarker(
                        [latitude, longitude],
                        {
                            radius: 10,
                            weight: 3,
                            fillOpacity: 0.85,
                        }
                    )
                    .addTo(weatherMap)
                    .bindPopup(
                        `<strong>${
                            escapeHtml(country.name)
                        }</strong><br>${
                            escapeHtml(
                                country.capital ?? '-'
                            )
                        }`
                    )
                    .openPopup();

                window.setTimeout(() => {
                    weatherMap.invalidateSize();
                }, 100);
            }

            function renderChart(forecast) {
                const chartElement =
                    document.getElementById(
                        'weatherForecastChart'
                    );

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
                                        type: 'line',

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

                                        tension: 0.3,

                                        yAxisID:
                                            'temperature',
                                    },

                                    {
                                        type: 'line',

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

                                        tension: 0.3,

                                        yAxisID:
                                            'temperature',
                                    },

                                    {
                                        type: 'bar',

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

                                scales: {
                                    temperature: {
                                        type: 'linear',

                                        position: 'left',

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
                if (forecast.length === 0) {
                    forecastTable.innerHTML = `
                        <tr>
                            <td
                                colspan="6"
                                class="text-center text-secondary"
                            >
                                Forecast tidak tersedia.
                            </td>
                        </tr>
                    `;

                    return;
                }

                forecastTable.innerHTML =
                    forecast.map((day) => `
                        <tr>
                            <td>
                                ${escapeHtml(
                                    formatDate(day.date)
                                )}
                            </td>

                            <td>
                                ${escapeHtml(
                                    day.description
                                )}
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
                    `).join('');
            }

            function renderWeather(data) {
                const country =
                    data.country;

                const current =
                    data.current;

                const risk =
                    data.risk;

                document.getElementById(
                    'weatherTemperature'
                ).textContent =
                    `${formatNumber(
                        current.temperature
                    )} °C`;

                document.getElementById(
                    'weatherCondition'
                ).textContent =
                    current.description;

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
                    `${risk.score}/100`;

                document.getElementById(
                    'weatherRiskLabel'
                ).textContent =
                    risk.label;

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
                    'weatherLocation'
                ).textContent =
                    `${country.name}`
                    + (
                        country.capital
                            ? ` — ${country.capital}`
                            : ''
                    )
                    + ` (${country.latitude}, `
                    + `${country.longitude})`;

                document.getElementById(
                    'weatherTimezone'
                ).textContent =
                    `Timezone: ${data.timezone}`;

                updateRiskAppearance(risk);

                renderRiskFactors(
                    risk.factors
                );

                renderMap(country);

                renderChart(
                    data.forecast
                );

                renderForecastTable(
                    data.forecast
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

                    const result =
                        await response.json();

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