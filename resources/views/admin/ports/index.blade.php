@extends('layouts.admin')

@section(
    'title',
    'Dataset Pelabuhan - Admin'
)

@section('content')
    <div class="topbar">
        <div>
            <h2>Dataset Pelabuhan</h2>

            <p>
                Tambah, edit, cari, dan hapus
                data pelabuhan.
            </p>
        </div>

        <span class="badge text-bg-primary fs-6">
            {{ $ports->total() }} Ports
        </span>
    </div>

    <section class="dashboard-card mb-4">
        <h3 class="mb-3">
            {{ $editingPort
                ? 'Edit Pelabuhan'
                : 'Tambah Pelabuhan'
            }}
        </h3>

        <form
            method="POST"
            action="{{
                $editingPort
                    ? route(
                        'admin.ports.update',
                        $editingPort
                    )
                    : route('admin.ports.store')
            }}"
        >
            @csrf

            @if ($editingPort)
                @method('PATCH')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Nama Pelabuhan
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old(
                            'name',
                            $editingPort?->name
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Negara
                    </label>

                    <input
                        type="text"
                        name="country_name"
                        class="form-control"
                        value="{{ old(
                            'country_name',
                            $editingPort?->country_name
                        ) }}"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Kode Negara
                    </label>

                    <input
                        type="text"
                        name="country_code"
                        class="form-control"
                        maxlength="3"
                        value="{{ old(
                            'country_code',
                            $editingPort?->country_code
                        ) }}"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Port Code
                    </label>

                    <input
                        type="text"
                        name="port_code"
                        class="form-control"
                        value="{{ old(
                            'port_code',
                            $editingPort?->port_code
                        ) }}"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Port Type
                    </label>

                    <input
                        type="text"
                        name="port_type"
                        class="form-control"
                        value="{{ old(
                            'port_type',
                            $editingPort?->port_type
                                ?? 'seaport'
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Latitude
                    </label>

                    <input
                        type="number"
                        step="0.0000001"
                        name="latitude"
                        class="form-control"
                        value="{{ old(
                            'latitude',
                            $editingPort?->latitude
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Longitude
                    </label>

                    <input
                        type="number"
                        step="0.0000001"
                        name="longitude"
                        class="form-control"
                        value="{{ old(
                            'longitude',
                            $editingPort?->longitude
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Congestion 0–100
                    </label>

                    <input
                        type="number"
                        name="congestion_level"
                        class="form-control"
                        min="0"
                        max="100"
                        value="{{ old(
                            'congestion_level',
                            $editingPort?->congestion_level
                                ?? 0
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Risk Level
                    </label>

                    <select
                        name="risk_level"
                        class="form-select"
                        required
                    >
                        @foreach ([
                            'low',
                            'medium',
                            'high',
                        ] as $risk)
                            <option
                                value="{{ $risk }}"
                                @selected(
                                    old(
                                        'risk_level',
                                        $editingPort?->risk_level
                                            ?? 'low'
                                    ) === $risk
                                )
                            >
                                {{ ucfirst($risk) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Source
                    </label>

                    <input
                        type="text"
                        name="source"
                        class="form-control"
                        value="{{ old(
                            'source',
                            $editingPort?->source
                                ?? 'Admin Input'
                        ) }}"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Catatan
                    </label>

                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"
                    >{{ old(
                        'notes',
                        $editingPort?->notes
                    ) }}</textarea>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">
                        {{ $editingPort
                            ? 'Simpan Perubahan'
                            : 'Tambah Pelabuhan'
                        }}
                    </button>

                    @if ($editingPort)
                        <a
                            href="{{ route('admin.ports.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Batal
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="dashboard-card">
        <form
            method="GET"
            class="row g-2 mb-4"
        >
            <div class="col-md-10">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Cari pelabuhan, negara, atau port code"
                >
            </div>

            <div class="col-md-2">
                <button
                    class="btn btn-outline-primary w-100"
                >
                    Cari
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Port</th>
                        <th>Country</th>
                        <th>Coordinates</th>
                        <th>Risk</th>
                        <th>Source</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($ports as $port)
                        <tr>
                            <td>
                                <strong>
                                    {{ $port->name }}
                                </strong>

                                <br>

                                <small class="text-secondary">
                                    {{ $port->port_code ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ $port->country_name ?? '-' }}
                            </td>

                            <td>
                                {{ $port->latitude }},
                                {{ $port->longitude }}
                            </td>

                            <td>
                                {{ ucfirst($port->risk_level) }}
                            </td>

                            <td>
                                {{ $port->source ?? '-' }}
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <a
                                        href="{{ route(
                                            'admin.ports.index',
                                            [
                                                'edit' => $port->id,
                                                'search' => $search,
                                            ]
                                        ) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.ports.destroy',
                                            $port
                                        ) }}"
                                        onsubmit="
                                            return confirm(
                                                'Hapus data pelabuhan ini?'
                                            );
                                        "
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-sm btn-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="text-center text-secondary"
                            >
                                Data pelabuhan tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $ports->links() }}
    </section>
@endsection