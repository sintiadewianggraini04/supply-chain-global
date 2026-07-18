<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPortController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input('search')
        );

        $ports = Port::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'country_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'port_code',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $editingPort = null;

        if ($request->filled('edit')) {
            $editingPort = Port::query()
                ->find($request->integer('edit'));
        }

        return view('admin.ports.index', [
            'ports' => $ports,
            'editingPort' => $editingPort,
            'search' => $search,
        ]);
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validatePort(
            $request
        );

        $port = new Port();

        $this->fillPort(
            $port,
            $validated
        );

        $port->save();

        return redirect()
            ->route('admin.ports.index')
            ->with(
                'success',
                'Data pelabuhan berhasil ditambahkan.'
            );
    }

    public function update(
        Request $request,
        Port $port
    ): RedirectResponse {
        $validated = $this->validatePort(
            $request
        );

        $this->fillPort(
            $port,
            $validated
        );

        $port->save();

        return redirect()
            ->route('admin.ports.index')
            ->with(
                'success',
                'Data pelabuhan berhasil diperbarui.'
            );
    }

    public function destroy(
        Port $port
    ): RedirectResponse {
        $port->delete();

        return redirect()
            ->route('admin.ports.index')
            ->with(
                'success',
                'Data pelabuhan berhasil dihapus.'
            );
    }

    private function validatePort(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'country_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'country_code' => [
                'nullable',
                'string',
                'max:3',
            ],

            'port_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'port_type' => [
                'required',
                'string',
                'max:50',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'congestion_level' => [
                'required',
                'integer',
                'between:0,100',
            ],

            'risk_level' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                ]),
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);
    }

    private function fillPort(
        Port $port,
        array $data
    ): void {
        $port->name = $data['name'];
        $port->country_name =
            $data['country_name'] ?? null;

        $port->country_code = strtoupper(
            $data['country_code'] ?? ''
        ) ?: null;

        $port->port_code = strtoupper(
            $data['port_code'] ?? ''
        ) ?: null;

        $port->port_type =
            $data['port_type'];

        $port->latitude =
            $data['latitude'];

        $port->longitude =
            $data['longitude'];

        $port->congestion_level =
            $data['congestion_level'];

        $port->risk_level =
            $data['risk_level'];

        $port->notes =
            $data['notes'] ?? null;

        $port->source =
            $data['source']
            ?? 'Admin Input';
    }
}