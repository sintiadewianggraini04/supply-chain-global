@extends('layouts.app')

@section('title', 'Country Intelligence - Global Supply Chain')

@section('content')
    <div class="topbar">
        <div>
            <h2>Country Intelligence</h2>

            <p>
                Monitoring data negara, mata uang, populasi, wilayah,
                dan indikator awal untuk analisis risiko rantai pasok global.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                Total Countries: {{ $totalCountries }}
            </span>
        </div>
    </div>

    <section class="dashboard-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="countrySearch" class="form-label">
                    Cari negara
                </label>

                <input
                    type="text"
                    id="countrySearch"
                    class="form-control"
                    placeholder="Contoh: Indonesia, China, Germany"
                >
            </div>

            <div class="col-md-4">
                <label for="regionFilter" class="form-label">
                    Filter region
                </label>

                <select id="regionFilter" class="form-select">
                    <option value="">Semua region</option>

                    @foreach ($regions as $region)
                        <option value="{{ $region }}">
                            {{ $region }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <button
                    type="button"
                    id="resetFilter"
                    class="btn btn-outline-secondary w-100"
                >
                    Reset Filter
                </button>
            </div>
        </div>
    </section>

    <section class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Global Countries Dataset</h3>

                <p class="text-secondary mb-0">
                    Data berasal dari REST Countries API yang sudah disimpan ke MySQL.
                </p>
            </div>

            <button
                type="button"
                id="reloadCountries"
                class="btn btn-primary"
            >
                Reload Data
            </button>
        </div>

        <div id="countryLoading" class="alert alert-info">
            Mengambil data negara...
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead>
                    <tr>
                        <th>Flag</th>
                        <th>Country</th>
                        <th>Code</th>
                        <th>Capital</th>
                        <th>Region</th>
                        <th>Currency</th>
                        <th>Population</th>
                    </tr>
                </thead>

                <tbody id="countryTableBody"></tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const searchInput = document.getElementById('countrySearch');
        const regionFilter = document.getElementById('regionFilter');
        const resetButton = document.getElementById('resetFilter');
        const reloadButton = document.getElementById('reloadCountries');
        const tableBody = document.getElementById('countryTableBody');
        const loadingBox = document.getElementById('countryLoading');

        async function loadCountries() {
            loadingBox.classList.remove('d-none');
            tableBody.innerHTML = '';

            const params = new URLSearchParams();

            if (searchInput.value.trim() !== '') {
                params.append('search', searchInput.value.trim());
            }

            if (regionFilter.value !== '') {
                params.append('region', regionFilter.value);
            }

            params.append('limit', '300');

            const response = await fetch(`/api/countries?${params.toString()}`);
            const result = await response.json();

            loadingBox.classList.add('d-none');

            if (! result.success) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Data negara gagal dimuat.
                        </td>
                    </tr>
                `;

                return;
            }

            if (result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-secondary">
                            Tidak ada data negara ditemukan.
                        </td>
                    </tr>
                `;

                return;
            }

            tableBody.innerHTML = result.data.map((country) => {
                const population = country.population
                    ? Number(country.population).toLocaleString('id-ID')
                    : '-';

                const flag = country.flag_url
                    ? `<img src="${country.flag_url}" alt="${country.name}" style="width: 36px;">`
                    : '-';

                const currency = country.currency_code
                    ? `${country.currency_code} - ${country.currency_name ?? '-'}`
                    : '-';

                return `
                    <tr>
                        <td>${flag}</td>
                        <td>
                            <strong>${country.name}</strong>
                            <br>
                            <span class="text-secondary small">
                                ${country.official_name ?? '-'}
                            </span>
                        </td>
                        <td>
                            <span class="badge text-bg-light">
                                ${country.cca3 ?? '-'}
                            </span>
                        </td>
                        <td>${country.capital ?? '-'}</td>
                        <td>
                            ${country.region ?? '-'}
                            <br>
                            <span class="text-secondary small">
                                ${country.subregion ?? '-'}
                            </span>
                        </td>
                        <td>${currency}</td>
                        <td>${population}</td>
                    </tr>
                `;
            }).join('');
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(window.countrySearchTimeout);

            window.countrySearchTimeout = setTimeout(() => {
                loadCountries();
            }, 500);
        });

        regionFilter.addEventListener('change', loadCountries);
        reloadButton.addEventListener('click', loadCountries);

        resetButton.addEventListener('click', () => {
            searchInput.value = '';
            regionFilter.value = '';
            loadCountries();
        });

        loadCountries();
    </script>
@endpush