<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\CountryComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CountryComparisonApiController extends Controller
{
    public function index(
        Request $request,
        CountryComparisonService $service
    ): JsonResponse {
        $validated = $request->validate([
            'country_a' => [
                'required',
                'integer',
                'exists:countries,id',
                'different:country_b',
            ],

            'country_b' => [
                'required',
                'integer',
                'exists:countries,id',
                'different:country_a',
            ],
        ]);

        $countryA = Country::query()
            ->where('is_active', true)
            ->findOrFail(
                $validated['country_a']
            );

        $countryB = Country::query()
            ->where('is_active', true)
            ->findOrFail(
                $validated['country_b']
            );

        try {
            $comparison = $service->compare(
                $countryA,
                $countryB
            );

            return response()->json([
                'success' => true,

                'message' =>
                    'Country comparison retrieved successfully.',

                'data' => $comparison,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,

                'message' =>
                    'Perbandingan negara gagal diproses.',

                'detail' => config('app.debug')
                    ? $exception->getMessage()
                    : null,
            ], 500);
        }
    }
}