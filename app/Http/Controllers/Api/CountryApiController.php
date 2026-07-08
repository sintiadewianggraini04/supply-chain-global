<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $countries = Country::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('official_name', 'like', "%{$search}%")
                        ->orWhere('cca2', 'like', "%{$search}%")
                        ->orWhere('cca3', 'like', "%{$search}%")
                        ->orWhere('capital', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('region'), function ($query) use ($request) {
                $query->where('region', $request->string('region'));
            })
            ->orderBy('name')
            ->limit((int) $request->input('limit', 100))
            ->get([
                'id',
                'name',
                'official_name',
                'cca2',
                'cca3',
                'capital',
                'region',
                'subregion',
                'currency_code',
                'currency_name',
                'currency_symbol',
                'population',
                'latitude',
                'longitude',
                'flag_url',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Countries retrieved successfully.',
            'data' => $countries,
            'meta' => [
                'count' => $countries->count(),
            ],
        ]);
    }
}