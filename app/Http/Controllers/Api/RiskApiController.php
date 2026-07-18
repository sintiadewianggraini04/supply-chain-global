<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\RiskScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RiskApiController extends Controller
{
    public function index(
        Request $request,
        RiskScoringService $riskScoringService
    ): JsonResponse {
        $validated = $request->validate([
            'country' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $countryReference = trim(
            $validated['country']
        );

        $countryCode = strtoupper(
            $countryReference
        );

        $country = Country::query()
            ->where('is_active', true)
            ->where(
                function ($query) use (
                    $countryReference,
                    $countryCode
                ) {
                    if (
                        ctype_digit(
                            $countryReference
                        )
                    ) {
                        $query->orWhere(
                            'id',
                            (int) $countryReference
                        );
                    }

                    $query
                        ->orWhere(
                            'cca2',
                            $countryCode
                        )
                        ->orWhere(
                            'cca3',
                            $countryCode
                        )
                        ->orWhere(
                            'name',
                            $countryReference
                        )
                        ->orWhere(
                            'official_name',
                            $countryReference
                        );
                }
            )
            ->first();

        if ($country === null) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Negara tidak ditemukan.',
            ], 404);
        }

        try {
            $risk = $riskScoringService
                ->scoreCountry($country);

            return response()->json([
                'success' => true,

                'message' =>
                    'Country risk score retrieved successfully.',

                'data' => [
                    'country' => [
                        'id' => $country->id,
                        'name' => $country->name,
                        'official_name' =>
                            $country->official_name,

                        'cca2' => $country->cca2,
                        'cca3' => $country->cca3,

                        'currency_code' =>
                            $country->currency_code,
                    ],

                    'risk' => $risk,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,

                'message' =>
                    'Risk score gagal dihitung.',

                'detail' => config('app.debug')
                    ? $exception->getMessage()
                    : null,
            ], 500);
        }
    }
}