@extends('layouts.app')

@section('title', 'Dashboard - Global Supply Chain')

@section('content')
    <div class="topbar">
        <div>
            <h2>Global Supply Chain Dashboard</h2>

            <p>
                Monitoring risiko logistik, ekonomi, cuaca,
                kurs, berita, dan pelabuhan global.
            </p>
        </div>

        <div>
            <select
                id="countrySelector"
                class="form-select"
                aria-label="Pilih negara"
            >
                <option value="IDN">Indonesia</option>
                <option value="CHN">China</option>
                <option value="DEU">Germany</option>
                <option value="AUS">Australia</option>
                <option value="USA">United States</option>
            </select>
        </div>
    </div>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Global Risk Score</h3>

                <p class="metric-value status-medium">
                    47
                </p>

                <p class="metric-description">
                    Medium Risk
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>GDP</h3>

                <p class="metric-value">
                    $1.37T
                </p>

                <p class="metric-description">
                    Data ekonomi negara
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Inflation</h3>

                <p class="metric-value">
                    2.8%
                </p>

                <p class="metric-description">
                    Perubahan harga tahunan
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Current Weather</h3>

                <p class="metric-value">
                    29°C
                </p>

                <p class="metric-description">
                    Wind speed: 14 km/h
                </p>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="mb-1">Global Risk Trend</h3>

                        <p class="text-secondary mb-0">
                            Perubahan indeks risiko selama 12 bulan.
                        </p>
                    </div>

                    <select class="form-select w-auto">
                        <option>12 Months</option>
                        <option>6 Months</option>
                        <option>30 Days</option>
                    </select>
                </div>

                <div class="chart-container">
                    <canvas id="riskTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dashboard-card">
                <h3 class="mb-1">Risk Components</h3>

                <p class="text-secondary">
                    Kontribusi indikator terhadap total risiko.
                </p>

                <div class="chart-container">
                    <canvas id="riskComponentChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="dashboard-card">
                <h3 class="mb-1">Global Monitoring Map</h3>

                <p class="text-secondary">
                    Lokasi negara dan pelabuhan yang dipantau.
                </p>

                <div id="globalMap"></div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="dashboard-card">
                <h3 class="mb-1">Latest Risk Alerts</h3>

                <p class="text-secondary">
                    Peringatan terbaru rantai pasok global.
                </p>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Indicator</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>China</td>
                                <td>Heavy Rain</td>
                                <td>
                                    <span class="badge text-bg-danger">
                                        High
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>Germany</td>
                                <td>Inflation</td>
                                <td>
                                    <span class="badge text-bg-warning">
                                        Medium
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>Australia</td>
                                <td>Currency</td>
                                <td>
                                    <span class="badge text-bg-success">
                                        Low
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>Indonesia</td>
                                <td>Port Delay</td>
                                <td>
                                    <span class="badge text-bg-warning">
                                        Medium
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const riskChartElement =
            document.getElementById('riskTrendChart');

        new window.Chart(riskChartElement, {
            type: 'line',

            data: {
                labels: [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec'
                ],

                datasets: [
                    {
                        label: 'Risk Score',

                        data: [
                            35,
                            42,
                            39,
                            50,
                            46,
                            58,
                            54,
                            62,
                            57,
                            65,
                            53,
                            47
                        ],

                        borderWidth: 3,
                        tension: 0.35,
                        fill: true
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },

                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const riskComponentElement =
            document.getElementById('riskComponentChart');

        new window.Chart(riskComponentElement, {
            type: 'doughnut',

            data: {
                labels: [
                    'Weather',
                    'Inflation',
                    'Political News',
                    'Currency'
                ],

                datasets: [
                    {
                        data: [
                            30,
                            20,
                            40,
                            10
                        ]
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const map = window.L.map('globalMap').setView(
            [-2.5489, 118.0149],
            4
        );

        window.L
            .tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution:
                        '&copy; OpenStreetMap contributors'
                }
            )
            .addTo(map);

        window.L
            .marker([-6.2088, 106.8456])
            .addTo(map)
            .bindPopup(
                '<strong>Jakarta, Indonesia</strong><br>Risk Score: 47'
            );
    </script>
@endpush