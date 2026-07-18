@extends('layouts.app')

@section('title', 'Port Monitoring - Global Supply Chain')

@section('content')
    <div class="topbar">
        <div>
            <h2>Port Monitoring</h2>

            <p>
                Monitoring lokasi pelabuhan, tingkat kemacetan,
                dan risiko operasional rantai pasok global.
            </p>
        </div>

        <div>
            <span
                id="portResultBadge"
                class="badge text-bg-primary fs-6"
            >
                Total Ports: {{ $totalPorts }}
            </span>
        </div>
    </div>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Total Ports</h3>

                <p
                    id="totalPortsValue"
                    class="metric-value"
                >
                    {{ $totalPorts }}
                </p>

                <p class="metric-description">
                    Pelabuhan yang ditampilkan
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Low Risk Ports</h3>

                <p
                    id="lowRiskPortsValue"
                    class="metric-value status-low"
                >
                    {{ $lowRiskPorts }}
                </p>

                <p class="metric-description">
                    Risiko operasional rendah
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Medium Risk Ports</h3>

                <p
                    id="mediumRiskPortsValue"
                    class="metric-value status-medium"
                >
                    {{ $mediumRiskPorts }}
                </p>

                <p class="metric-description">
                    Risiko operasional sedang
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>High Risk Ports</h3>

                <p
                    id="highRiskPortsValue"
                    class="metric-value status-high"
                >
                    {{ $highRiskPorts }}
                </p>

                <p class="metric-description">
                    Risiko operasional tinggi
                </p>
            </div>
        </div>
    </section>

    <section class="dashboard-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label
                    for="portSearch"
                    class="form-label"
                >
                    Cari Pelabuhan
                </label>

                <input
                    type="text"
                    id="portSearch"
                    class="form-control"
                    placeholder="Nama pelabuhan, negara, atau kode..."
                >
            </div>

            <div class="col-md-3">
                <label
                    for="portCountry"
                    class="form-label"
                >
                    Negara
                </label>

                <select
                    id="portCountry"
                    class="form-select"
                >
                    <option value="">
                        Semua negara
                    </option>

                    @foreach ($countries as $country)
                        <option
                            value="{{ $country->country_code }}"
                        >
                            {{ $country->country_name }}
                            ({{ $country->country_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label
                    for="portRisk"
                    class="form-label"
                >
                    Risk Level
                </label>

                <select
                    id="portRisk"
                    class="form-select"
                >
                    <option value="">
                        Semua risiko
                    </option>

                    <option value="low">
                        Low Risk
                    </option>

                    <option value="medium">
                        Medium Risk
                    </option>

                    <option value="high">
                        High Risk
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <button
                    type="button"
                    id="resetPortFilter"
                    class="btn btn-outline-secondary w-100"
                >
                    Reset Filter
                </button>
            </div>
        </div>
    </section>

    <div
        id="portLoading"
        class="alert alert-info"
    >
        Mengambil data pelabuhan...
    </div>

    <div
        id="portError"
        class="alert alert-danger d-none"
    ></div>

    <section class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="dashboard-card">
                <div
                    class="d-flex justify-content-between
                    align-items-start gap-3 mb-3"
                >
                    <div>
                        <h3 class="mb-1">
                            Global Port Map
                        </h3>

                        <p class="text-secondary mb-0">
                            Klik marker untuk melihat informasi
                            detail pelabuhan.
                        </p>
                    </div>

                    <div class="port-map-legend">
                        <span>
                            <i class="legend-dot risk-low-dot"></i>
                            Low
                        </span>

                        <span>
                            <i class="legend-dot risk-medium-dot"></i>
                            Medium
                        </span>

                        <span>
                            <i class="legend-dot risk-high-dot"></i>
                            High
                        </span>
                    </div>
                </div>

                <div id="portMap"></div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dashboard-card">
                <h3 class="mb-1">
                    Port Details
                </h3>

                <p class="text-secondary">
                    Pilih pelabuhan dari marker atau daftar.
                </p>

                <div
                    id="selectedPortDetails"
                    class="selected-port-details"
                >
                    <div class="text-secondary">
                        Belum ada pelabuhan yang dipilih.
                    </div>
                </div>

                <hr>

                <h3 class="mb-3">
                    Port List
                </h3>

                <div
                    id="portList"
                    class="port-list"
                >
                    <div class="text-secondary">
                        Menunggu data pelabuhan.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-card">
        <div
            class="d-flex justify-content-between
            align-items-start gap-3 mb-3"
        >
            <div>
                <h3 class="mb-1">
                    Port Dataset
                </h3>

                <p class="text-secondary mb-0">
                    Data pelabuhan yang tersimpan di database.
                </p>
            </div>

            <div
                id="portTableCount"
                class="small text-secondary"
            >
                0 data
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Pelabuhan</th>
                        <th>Negara</th>
                        <th>Kode</th>
                        <th>Jenis</th>
                        <th>Koordinat</th>
                        <th>Kemacetan</th>
                        <th>Risiko</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="portTableBody">
                    <tr>
                        <td
                            colspan="8"
                            class="text-center text-secondary"
                        >
                            Mengambil data pelabuhan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const portApiUrl =
            @json(route('api.ports.index'));

        const searchInput =
            document.getElementById('portSearch');

        const countryFilter =
            document.getElementById('portCountry');

        const riskFilter =
            document.getElementById('portRisk');

        const resetButton =
            document.getElementById('resetPortFilter');

        const loadingBox =
            document.getElementById('portLoading');

        const errorBox =
            document.getElementById('portError');

        const portList =
            document.getElementById('portList');

        const portTableBody =
            document.getElementById('portTableBody');

        const selectedPortDetails =
            document.getElementById(
                'selectedPortDetails'
            );

        const portResultBadge =
            document.getElementById(
                'portResultBadge'
            );

        const portTableCount =
            document.getElementById(
                'portTableCount'
            );

        let currentPorts = [];
        let searchTimer = null;
        let portMap = null;
        let markerLayer = null;
        let markers = {};

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

        function formatNumber(value, decimals = 1) {
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

        function formatRiskLabel(riskLevel) {
            if (riskLevel === 'high') {
                return 'High Risk';
            }

            if (riskLevel === 'medium') {
                return 'Medium Risk';
            }

            return 'Low Risk';
        }

        function riskBadge(riskLevel) {
            if (riskLevel === 'high') {
                return `
                    <span class="badge text-bg-danger">
                        High Risk
                    </span>
                `;
            }

            if (riskLevel === 'medium') {
                return `
                    <span class="badge text-bg-warning">
                        Medium Risk
                    </span>
                `;
            }

            return `
                <span class="badge text-bg-success">
                    Low Risk
                </span>
            `;
        }

        function riskColor(riskLevel) {
            if (riskLevel === 'high') {
                return '#dc2626';
            }

            if (riskLevel === 'medium') {
                return '#d97706';
            }

            return '#16a34a';
        }

        function initializeMap() {
            portMap = window.L
                .map('portMap', {
                    worldCopyJump: true,
                })
                .setView([20, 0], 2);

            window.L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,

                    attribution:
                        '&copy; OpenStreetMap contributors',
                }
            ).addTo(portMap);

            markerLayer = window.L
                .layerGroup()
                .addTo(portMap);
        }

        function renderMarkers(ports) {
            markerLayer.clearLayers();
            markers = {};

            const validPorts = ports.filter(
                (port) =>
                    port.latitude !== null
                    && port.longitude !== null
            );

            if (validPorts.length === 0) {
                portMap.setView([20, 0], 2);

                return;
            }

            const bounds = [];

            validPorts.forEach((port) => {
                const latitude =
                    Number(port.latitude);

                const longitude =
                    Number(port.longitude);

                const color =
                    riskColor(port.risk_level);

                const marker = window.L
                    .circleMarker(
                        [latitude, longitude],
                        {
                            radius: 9,
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.8,
                            weight: 3,
                        }
                    )
                    .addTo(markerLayer);

                marker.bindPopup(`
                    <div class="port-popup">
                        <strong>
                            ${escapeHtml(port.name)}
                        </strong>

                        <div>
                            ${escapeHtml(
                                port.country_name ?? '-'
                            )}
                            (${escapeHtml(
                                port.country_code ?? '-'
                            )})
                        </div>

                        <div>
                            Port Code:
                            ${escapeHtml(
                                port.port_code ?? '-'
                            )}
                        </div>

                        <div>
                            Congestion:
                            ${formatNumber(
                                port.congestion_level,
                                0
                            )}%
                        </div>

                        <div class="mt-2">
                            ${riskBadge(port.risk_level)}
                        </div>
                    </div>
                `);

                marker.on('click', () => {
                    selectPort(port.id, false);
                });

                markers[port.id] = marker;

                bounds.push([
                    latitude,
                    longitude,
                ]);
            });

            if (bounds.length === 1) {
                portMap.setView(bounds[0], 7);
            } else {
                portMap.fitBounds(bounds, {
                    padding: [40, 40],
                    maxZoom: 6,
                });
            }

            window.setTimeout(() => {
                portMap.invalidateSize();
            }, 100);
        }

        function renderPortList(ports) {
            if (ports.length === 0) {
                portList.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        Data pelabuhan tidak ditemukan.
                    </div>
                `;

                return;
            }

            portList.innerHTML = ports.map((port) => `
                <button
                    type="button"
                    class="port-list-item"
                    data-port-id="${port.id}"
                >
                    <div
                        class="d-flex justify-content-between
                        align-items-start gap-2"
                    >
                        <div>
                            <strong>
                                ${escapeHtml(port.name)}
                            </strong>

                            <small>
                                ${escapeHtml(
                                    port.country_name ?? '-'
                                )}
                                ·
                                ${escapeHtml(
                                    port.port_code ?? '-'
                                )}
                            </small>
                        </div>

                        ${riskBadge(port.risk_level)}
                    </div>
                </button>
            `).join('');

            portList
                .querySelectorAll('[data-port-id]')
                .forEach((button) => {
                    button.addEventListener(
                        'click',
                        () => {
                            selectPort(
                                Number(
                                    button.dataset.portId
                                )
                            );
                        }
                    );
                });
        }

        function renderTable(ports) {
            portTableCount.textContent =
                `${ports.length} data`;

            if (ports.length === 0) {
                portTableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="8"
                            class="text-center text-secondary"
                        >
                            Data pelabuhan tidak ditemukan.
                        </td>
                    </tr>
                `;

                return;
            }

            portTableBody.innerHTML = ports.map((port) => `
                <tr>
                    <td>
                        <strong>
                            ${escapeHtml(port.name)}
                        </strong>

                        <div class="small text-secondary">
                            ${escapeHtml(
                                port.source ?? '-'
                            )}
                        </div>
                    </td>

                    <td>
                        ${escapeHtml(
                            port.country_name ?? '-'
                        )}

                        <div class="small text-secondary">
                            ${escapeHtml(
                                port.country_code ?? '-'
                            )}
                        </div>
                    </td>

                    <td>
                        ${escapeHtml(
                            port.port_code ?? '-'
                        )}
                    </td>

                    <td>
                        ${escapeHtml(
                            port.port_type ?? '-'
                        )}
                    </td>

                    <td>
                        ${formatNumber(port.latitude, 4)},
                        ${formatNumber(port.longitude, 4)}
                    </td>

                    <td>
                        <div class="port-congestion">
                            <div
                                class="progress"
                                role="progressbar"
                            >
                                <div
                                    class="progress-bar"
                                    style="width: ${
                                        Number(
                                            port.congestion_level
                                        )
                                    }%"
                                ></div>
                            </div>

                            <span>
                                ${formatNumber(
                                    port.congestion_level,
                                    0
                                )}%
                            </span>
                        </div>
                    </td>

                    <td>
                        ${riskBadge(port.risk_level)}
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-sm
                            btn-outline-primary
                            show-port-button"
                            data-port-id="${port.id}"
                        >
                            Lihat Peta
                        </button>
                    </td>
                </tr>
            `).join('');

            portTableBody
                .querySelectorAll('.show-port-button')
                .forEach((button) => {
                    button.addEventListener(
                        'click',
                        () => {
                            selectPort(
                                Number(
                                    button.dataset.portId
                                )
                            );
                        }
                    );
                });
        }

        function selectPort(
            portId,
            openPopup = true
        ) {
            const port = currentPorts.find(
                (item) => item.id === portId
            );

            if (! port) {
                return;
            }

            selectedPortDetails.innerHTML = `
                <div class="selected-port-header">
                    <div>
                        <h4>
                            ${escapeHtml(port.name)}
                        </h4>

                        <p>
                            ${escapeHtml(
                                port.country_name ?? '-'
                            )}
                            (${escapeHtml(
                                port.country_code ?? '-'
                            )})
                        </p>
                    </div>

                    ${riskBadge(port.risk_level)}
                </div>

                <dl class="port-detail-list">
                    <div>
                        <dt>Port Code</dt>
                        <dd>
                            ${escapeHtml(
                                port.port_code ?? '-'
                            )}
                        </dd>
                    </div>

                    <div>
                        <dt>Port Type</dt>
                        <dd>
                            ${escapeHtml(
                                port.port_type ?? '-'
                            )}
                        </dd>
                    </div>

                    <div>
                        <dt>Congestion Level</dt>
                        <dd>
                            ${formatNumber(
                                port.congestion_level,
                                0
                            )}%
                        </dd>
                    </div>

                    <div>
                        <dt>Risk Level</dt>
                        <dd>
                            ${formatRiskLabel(
                                port.risk_level
                            )}
                        </dd>
                    </div>

                    <div>
                        <dt>Coordinates</dt>
                        <dd>
                            ${formatNumber(
                                port.latitude,
                                5
                            )},
                            ${formatNumber(
                                port.longitude,
                                5
                            )}
                        </dd>
                    </div>

                    <div>
                        <dt>Notes</dt>
                        <dd>
                            ${escapeHtml(
                                port.notes
                                ?? 'Tidak ada catatan.'
                            )}
                        </dd>
                    </div>
                </dl>
            `;

            document
                .querySelectorAll('.port-list-item')
                .forEach((item) => {
                    item.classList.toggle(
                        'active',
                        Number(item.dataset.portId)
                            === portId
                    );
                });

            const marker = markers[portId];

            if (marker) {
                const location = marker.getLatLng();

                portMap.setView(
                    location,
                    7,
                    {
                        animate: true,
                    }
                );

                if (openPopup) {
                    marker.openPopup();
                }
            }
        }

        function updateStatistics(meta) {
            document.getElementById(
                'totalPortsValue'
            ).textContent = meta.count;

            document.getElementById(
                'lowRiskPortsValue'
            ).textContent =
                meta.risk_counts.low;

            document.getElementById(
                'mediumRiskPortsValue'
            ).textContent =
                meta.risk_counts.medium;

            document.getElementById(
                'highRiskPortsValue'
            ).textContent =
                meta.risk_counts.high;

            portResultBadge.textContent =
                `Ports Found: ${meta.count}`;
        }

        function setLoading(isLoading) {
            loadingBox.classList.toggle(
                'd-none',
                ! isLoading
            );
        }

        function showError(message) {
            errorBox.textContent = message;

            errorBox.classList.remove('d-none');
        }

        function hideError() {
            errorBox.textContent = '';

            errorBox.classList.add('d-none');
        }

        async function loadPorts() {
            hideError();
            setLoading(true);

            const params = new URLSearchParams();

            if (searchInput.value.trim() !== '') {
                params.append(
                    'search',
                    searchInput.value.trim()
                );
            }

            if (countryFilter.value !== '') {
                params.append(
                    'country',
                    countryFilter.value
                );
            }

            if (riskFilter.value !== '') {
                params.append(
                    'risk_level',
                    riskFilter.value
                );
            }

            try {
                const response = await fetch(
                    `${portApiUrl}?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                const result = await response.json();

                if (! response.ok || ! result.success) {
                    throw new Error(
                        result.message
                        ?? 'Data pelabuhan gagal dimuat.'
                    );
                }

                currentPorts = result.data;

                updateStatistics(result.meta);
                renderMarkers(currentPorts);
                renderPortList(currentPorts);
                renderTable(currentPorts);

                selectedPortDetails.innerHTML = `
                    <div class="text-secondary">
                        Pilih pelabuhan dari marker,
                        daftar, atau tabel.
                    </div>
                `;
            } catch (error) {
                showError(
                    error.message
                    ?? 'Terjadi kesalahan saat memuat pelabuhan.'
                );
            } finally {
                setLoading(false);
            }
        }

        searchInput.addEventListener(
            'input',
            () => {
                window.clearTimeout(searchTimer);

                searchTimer = window.setTimeout(
                    loadPorts,
                    400
                );
            }
        );

        countryFilter.addEventListener(
            'change',
            loadPorts
        );

        riskFilter.addEventListener(
            'change',
            loadPorts
        );

        resetButton.addEventListener(
            'click',
            () => {
                searchInput.value = '';
                countryFilter.value = '';
                riskFilter.value = '';

                loadPorts();
            }
        );

        initializeMap();
        loadPorts();
    </script>
@endpush