@extends('layouts.app')

@section(
    'title',
    'Favorite Monitoring List - Global Supply Chain'
)

@section('content')
    <div class="topbar">
        <div>
            <h2>Favorite Monitoring List</h2>

            <p>
                Daftar negara yang disimpan untuk
                pemantauan rantai pasok.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                {{ $totalFavorites }} Countries Monitored
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <section class="dashboard-card">
        <div
            class="d-flex justify-content-between
            align-items-center mb-4"
        >
            <div>
                <h3 class="mb-1">
                    Monitored Countries
                </h3>

                <p class="text-secondary mb-0">
                    Negara yang telah ditambahkan
                    ke daftar pemantauan.
                </p>
            </div>

            <a
                href="{{ route('countries.index') }}"
                class="btn btn-primary"
            >
                Add Country
            </a>
        </div>

        @if ($favorites->isEmpty())
            <div class="alert alert-info mb-0">
                Belum ada negara yang disimpan.

                <a
                    href="{{ route('countries.index') }}"
                    class="alert-link"
                >
                    Pilih negara untuk dipantau.
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table
                    class="table align-middle table-hover"
                >
                    <thead>
                        <tr>
                            <th>Flag</th>
                            <th>Country</th>
                            <th>Capital</th>
                            <th>Region</th>
                            <th>Currency</th>
                            <th>Population</th>
                            <th>Added</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($favorites as $favorite)
                            @php
                                $country = $favorite->country;
                            @endphp

                            <tr>
                                <td>
                                    @if ($country->flag_url)
                                        <img
                                            src="{{ $country->flag_url }}"
                                            alt="{{ $country->name }}"
                                            style="
                                                width: 42px;
                                                max-height: 28px;
                                                object-fit: cover;
                                            "
                                        >
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <strong>
                                        {{ $country->name }}
                                    </strong>

                                    <br>

                                    <span
                                        class="text-secondary small"
                                    >
                                        {{ $country->official_name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $country->capital ?? '-' }}
                                </td>

                                <td>
                                    {{ $country->region ?? '-' }}

                                    <br>

                                    <span
                                        class="text-secondary small"
                                    >
                                        {{ $country->subregion ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    @if ($country->currency_code)
                                        {{ $country->currency_code }}

                                        -

                                        {{ $country->currency_name ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $country->population
                                        ? number_format(
                                            $country->population,
                                            0,
                                            ',',
                                            '.'
                                        )
                                        : '-'
                                    }}
                                </td>

                                <td>
                                    {{ $favorite->created_at
                                        ->format('d M Y H:i')
                                    }}
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'favorites.destroy',
                                            $country
                                        ) }}"
                                        onsubmit="
                                            return confirm(
                                                'Hapus negara ini dari favorit?'
                                            );
                                        "
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection