<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:3',
            ],

            'risk_level' => [
                'nullable',
                'in:low,medium,high',
            ],
        ]);

        $search = trim(
            (string) ($validated['search'] ?? '')
        );

        $country = strtoupper(
            trim((string) ($validated['country'] ?? ''))
        );

        $riskLevel = $validated['risk_level'] ?? null;

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
                                    'country_code',
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
            ->when(
                $country !== '',
                function ($query) use ($country) {
                    $query->where(
                        'country_code',
                        $country
                    );
                }
            )
            ->when(
                $riskLevel !== null,
                function ($query) use ($riskLevel) {
                    $query->where(
                        'risk_level',
                        $riskLevel
                    );
                }
            )
            ->orderBy('country_name')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'country_name',
                'country_code',
                'port_code',
                'port_type',
                'latitude',
                'longitude',
                'congestion_level',
                'risk_level',
                'notes',
                'source',
            ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Data pelabuhan berhasil diambil.',

            'data' => $ports,

            'meta' => [
                'count' => $ports->count(),

                'risk_counts' => [
                    'low' => $ports
                        ->where('risk_level', 'low')
                        ->count(),

                    'medium' => $ports
                        ->where('risk_level', 'medium')
                        ->count(),

                    'high' => $ports
                        ->where('risk_level', 'high')
                        ->count(),
                ],
            ],
        ]);
    }
}