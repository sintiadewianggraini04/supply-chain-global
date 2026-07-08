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
        $ports = Port::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('country_name', 'like', "%{$search}%")
                        ->orWhere('country_code', 'like', "%{$search}%")
                        ->orWhere('port_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('country'), function ($query) use ($request) {
                $query->where(
                    'country_code',
                    strtoupper((string) $request->input('country'))
                );
            })
            ->when($request->filled('risk_level'), function ($query) use ($request) {
                $query->where('risk_level', $request->input('risk_level'));
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Ports retrieved successfully.',
            'data' => $ports,
            'meta' => [
                'count' => $ports->count(),
            ],
        ]);
    }
}