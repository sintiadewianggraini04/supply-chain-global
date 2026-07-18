@extends('layouts.app')

@section(
    'title',
    'Country Intelligence - Global Supply Chain'
)

@section('content')
    <div class="topbar">
        <div>
            <h2>Country Intelligence</h2>

            <p>
                Monitoring data negara, mata uang, populasi,
                wilayah, dan negara yang dipantau.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                Total Countries: {{ $totalCountries }}
            </span>
        </div>
    </div>

    <div
        id="favoriteMessage"
        class="alert d-none"
    ></div>

    <section class="dashboard-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label
                    for="countrySearch"
                    class="form-label"
                >
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
                <label
                    for="regionFilter"
                    class="form-label"
                >
                    Filter region
                </label>

                <select
                    id="regionFilter"
                    class="form-select"
                >
                    <option value="">
                        Semua region
                    </option>

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
        <div
            class="d-flex justify-content-between
            align-items-center mb-3"
        >
            <div>
                <h3 class="mb-1">
                    Global Countries Dataset
                </h3>

                <p class="text-secondary mb-0">
                    Pilih negara yang ingin dimasukkan
                    ke Favorite Monitoring List.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a
                    href="{{ route('favorites.index') }}"
                    class="btn btn-outline-primary"
                >
                    View Favorites
                </a>

                <button
                    type="button"
                    id="reloadCountries"
                    class="btn btn-primary"
                >
                    Reload Data
                </button>
            </div>
        </div>

        <div
            id="countryLoading"
            class="alert alert-info"
        >
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
                        <th>Monitoring</th>
                    </tr>
                </thead>

                <tbody id="countryTableBody"></tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const countriesApiUrl =
            @json(route('api.countries.index'));

        const favoritesBaseUrl =
            @json(url('/favorites'));

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');

        const searchInput =
            document.getElementById(
                'countrySearch'
            );

        const regionFilter =
            document.getElementById(
                'regionFilter'
            );

        const resetButton =
            document.getElementById(
                'resetFilter'
            );

        const reloadButton =
            document.getElementById(
                'reloadCountries'
            );

        const tableBody =
            document.getElementById(
                'countryTableBody'
            );

        const loadingBox =
            document.getElementById(
                'countryLoading'
            );

        const messageBox =
            document.getElementById(
                'favoriteMessage'
            );

        let searchTimeout = null;

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

        function safeImageUrl(value) {
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

        function showMessage(
            message,
            type = 'success'
        ) {
            messageBox.textContent = message;

            messageBox.className =
                `alert alert-${type}`;

            window.setTimeout(() => {
                messageBox.classList.add('d-none');
            }, 3000);
        }

        function favoriteButton(country) {
            const isFavorite =
                Boolean(country.favorite_exists);

            if (isFavorite) {
                return `
                    <button
                        type="button"
                        class="btn btn-sm btn-danger
                        favorite-button"
                        data-country-id="${country.id}"
                        data-favorite="1"
                    >
                        Remove Favorite
                    </button>
                `;
            }

            return `
                <button
                    type="button"
                    class="btn btn-sm
                    btn-outline-primary
                    favorite-button"
                    data-country-id="${country.id}"
                    data-favorite="0"
                >
                    Add Favorite
                </button>
            `;
        }

        async function loadCountries() {
            loadingBox.classList.remove(
                'd-none'
            );

            tableBody.innerHTML = '';

            const params =
                new URLSearchParams();

            if (
                searchInput.value.trim()
                !== ''
            ) {
                params.append(
                    'search',
                    searchInput.value.trim()
                );
            }

            if (regionFilter.value !== '') {
                params.append(
                    'region',
                    regionFilter.value
                );
            }

            params.append('limit', '300');

            try {
                const response = await fetch(
                    `${countriesApiUrl}?${params.toString()}`,
                    {
                        headers: {
                            Accept: 'application/json',
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
                        ?? 'Data negara gagal dimuat.'
                    );
                }

                if (result.data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td
                                colspan="8"
                                class="text-center
                                text-secondary"
                            >
                                Tidak ada data negara ditemukan.
                            </td>
                        </tr>
                    `;

                    return;
                }

                tableBody.innerHTML =
                    result.data.map((country) => {
                        const population =
                            country.population
                                ? Number(
                                    country.population
                                ).toLocaleString(
                                    'id-ID'
                                )
                                : '-';

                        const flagUrl =
                            safeImageUrl(
                                country.flag_url
                            );

                        const flag = flagUrl
                            ? `
                                <img
                                    src="${flagUrl}"
                                    alt="${escapeHtml(
                                        country.name
                                    )}"
                                    style="width: 36px;
                                    max-height: 26px;
                                    object-fit: cover;"
                                >
                            `
                            : '-';

                        const currency =
                            country.currency_code
                                ? `
                                    ${escapeHtml(
                                        country.currency_code
                                    )}
                                    -
                                    ${escapeHtml(
                                        country.currency_name
                                        ?? '-'
                                    )}
                                `
                                : '-';

                        return `
                            <tr>
                                <td>${flag}</td>

                                <td>
                                    <strong>
                                        ${escapeHtml(
                                            country.name
                                        )}
                                    </strong>

                                    <br>

                                    <span
                                        class="text-secondary small"
                                    >
                                        ${escapeHtml(
                                            country.official_name
                                            ?? '-'
                                        )}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge text-bg-light"
                                    >
                                        ${escapeHtml(
                                            country.cca3
                                            ?? '-'
                                        )}
                                    </span>
                                </td>

                                <td>
                                    ${escapeHtml(
                                        country.capital
                                        ?? '-'
                                    )}
                                </td>

                                <td>
                                    ${escapeHtml(
                                        country.region
                                        ?? '-'
                                    )}

                                    <br>

                                    <span
                                        class="text-secondary small"
                                    >
                                        ${escapeHtml(
                                            country.subregion
                                            ?? '-'
                                        )}
                                    </span>
                                </td>

                                <td>${currency}</td>

                                <td>${population}</td>

                                <td>
                                    ${favoriteButton(
                                        country
                                    )}
                                </td>
                            </tr>
                        `;
                    }).join('');
            } catch (error) {
                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="8"
                            class="text-center text-danger"
                        >
                            ${escapeHtml(
                                error.message
                                ?? 'Data negara gagal dimuat.'
                            )}
                        </td>
                    </tr>
                `;
            } finally {
                loadingBox.classList.add(
                    'd-none'
                );
            }
        }

        async function toggleFavorite(button) {
            const countryId =
                button.dataset.countryId;

            const isFavorite =
                button.dataset.favorite === '1';

            button.disabled = true;

            try {
                const response = await fetch(
                    `${favoritesBaseUrl}/${countryId}`,
                    {
                        method: isFavorite
                            ? 'DELETE'
                            : 'POST',

                        headers: {
                            Accept: 'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,
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
                        ?? 'Favorite gagal diperbarui.'
                    );
                }

                if (result.is_favorite) {
                    button.dataset.favorite = '1';

                    button.textContent =
                        'Remove Favorite';

                    button.className =
                        'btn btn-sm btn-danger '
                        + 'favorite-button';
                } else {
                    button.dataset.favorite = '0';

                    button.textContent =
                        'Add Favorite';

                    button.className =
                        'btn btn-sm '
                        + 'btn-outline-primary '
                        + 'favorite-button';
                }

                showMessage(result.message);
            } catch (error) {
                showMessage(
                    error.message
                    ?? 'Terjadi kesalahan.',
                    'danger'
                );
            } finally {
                button.disabled = false;
            }
        }

        tableBody.addEventListener(
            'click',
            (event) => {
                const button =
                    event.target.closest(
                        '.favorite-button'
                    );

                if (! button) {
                    return;
                }

                toggleFavorite(button);
            }
        );

        searchInput.addEventListener(
            'input',
            () => {
                window.clearTimeout(
                    searchTimeout
                );

                searchTimeout =
                    window.setTimeout(
                        loadCountries,
                        500
                    );
            }
        );

        regionFilter.addEventListener(
            'change',
            loadCountries
        );

        reloadButton.addEventListener(
            'click',
            loadCountries
        );

        resetButton.addEventListener(
            'click',
            () => {
                searchInput.value = '';
                regionFilter.value = '';

                loadCountries();
            }
        );

        loadCountries();
    </script>
@endpush