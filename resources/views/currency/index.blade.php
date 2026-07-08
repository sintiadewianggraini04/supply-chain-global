@extends('layouts.app')

@section('title', 'Currency Impact - Global Supply Chain')

@section('content')
    <div class="topbar">
        <div>
            <h2>Currency Impact Dashboard</h2>

            <p>
                Monitoring nilai tukar mata uang terhadap USD untuk melihat dampaknya
                terhadap biaya impor, ekspor, dan rantai pasok global.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                Total Rates: {{ $totalRates }}
            </span>
        </div>
    </div>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Base Currency</h3>
                <p class="metric-value">USD</p>
                <p class="metric-description">Mata uang dasar analisis</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Tracked Currencies</h3>
                <p class="metric-value" id="trackedCurrencies">-</p>
                <p class="metric-description">Jumlah mata uang dipantau</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>USD to IDR</h3>
                <p class="metric-value" id="usdIdrRate">-</p>
                <p class="metric-description">Kurs USD terhadap Rupiah</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Data Source</h3>
                <p class="metric-value" style="font-size: 22px;">ExchangeRate</p>
                <p class="metric-description">API kurs global</p>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-xl-7">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="mb-1">Currency Comparison</h3>
                        <p class="text-secondary mb-0">
                            Perbandingan kurs beberapa mata uang terhadap USD.
                        </p>
                    </div>

                    <button type="button" id="reloadCurrency" class="btn btn-primary">
                        Reload
                    </button>
                </div>

                <div class="chart-container">
                    <canvas id="currencyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="dashboard-card">
                <h3 class="mb-1">Latest Exchange Rates</h3>

                <p class="text-secondary">
                    Data kurs terbaru yang tersimpan di tabel exchange_rates.
                </p>

                <div id="currencyLoading" class="alert alert-info">
                    Mengambil data kurs...
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Base</th>
                                <th>Target</th>
                                <th>Rate</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody id="currencyTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const reloadButton = document.getElementById('reloadCurrency');
        const tableBody = document.getElementById('currencyTableBody');
        const loadingBox = document.getElementById('currencyLoading');
        const trackedCurrencies = document.getElementById('trackedCurrencies');
        const usdIdrRate = document.getElementById('usdIdrRate');

        let currencyChart = null;

        async function loadCurrencyRates() {
            loadingBox.classList.remove('d-none');
            tableBody.innerHTML = '';

            const response = await fetch('/api/currency');
            const result = await response.json();

            loadingBox.classList.add('d-none');

            if (! result.success || result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-secondary">
                            Data kurs belum tersedia. Jalankan php artisan currency:sync.
                        </td>
                    </tr>
                `;

                return;
            }

            trackedCurrencies.textContent = result.data.length;

            const idrRate = result.data.find((rate) => {
                return rate.target_currency === 'IDR';
            });

            usdIdrRate.textContent = idrRate
                ? Number(idrRate.rate).toLocaleString('id-ID')
                : '-';

            tableBody.innerHTML = result.data.map((rate) => {
                return `
                    <tr>
                        <td>${rate.base_currency}</td>
                        <td><strong>${rate.target_currency}</strong></td>
                        <td>
                            ${Number(rate.rate).toLocaleString('id-ID', {
                                maximumFractionDigits: 4
                            })}
                        </td>
                        <td>${rate.rate_date}</td>
                    </tr>
                `;
            }).join('');

            renderCurrencyChart(result.data);
        }

        function renderCurrencyChart(rates) {
            const chartElement = document.getElementById('currencyChart');

            const labels = rates.map((rate) => rate.target_currency);
            const values = rates.map((rate) => Number(rate.rate));

            if (currencyChart) {
                currencyChart.destroy();
            }

            currencyChart = new window.Chart(chartElement, {
                type: 'bar',

                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Exchange Rate vs USD',
                            data: values,
                            borderWidth: 1
                        }
                    ]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false
                        }
                    },

                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        reloadButton.addEventListener('click', loadCurrencyRates);

        loadCurrencyRates();
    </script>
@endpush