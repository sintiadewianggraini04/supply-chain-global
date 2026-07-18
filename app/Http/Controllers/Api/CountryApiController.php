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
        $limit = max(
            1,
            min(
                (int) $request->input('limit', 100),
                300
            )
        );

        $countries = Country::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        (string) $request->input('search')
                    );

                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'official_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'cca2',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'cca3',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'capital',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $request->filled('region'),
                function ($query) use ($request) {
                    $query->where(
                        'region',
                        (string) $request->input('region')
                    );
                }
            )
            ->where('is_active', true)
            ->select([
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
            ])
            ->withExists('favorite')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' =>
                'Countries retrieved successfully.',
            'data' => $countries,
            'meta' => [
                'count' => $countries->count(),
            ],
        ]);
    }
}