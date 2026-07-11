<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\OpenMeteoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WeatherApiController extends Controller
{
    public function index(
        Request $request,
        OpenMeteoService $openMeteoService
    ): JsonResponse {
        $validated = $request->validate([
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],
        ]);

        $country = Country::query()
            ->whereKey($validated['country_id'])
            ->where('is_active', true)
            ->first();

        if (! $country) {
            return response()->json([
                'success' => false,
                'message' => 'Negara tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        if (
            $country->latitude === null
            || $country->longitude === null
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat negara belum tersedia.',
            ], 422);
        }

        try {
            $weather = $openMeteoService->getForecast(
                (float) $country->latitude,
                (float) $country->longitude
            );

            return response()->json([
                'success' => true,

                'data' => array_merge(
                    [
                        'country' => [
                            'id' => $country->id,
                            'name' => $country->name,
                            'official_name' => $country->official_name,
                            'cca2' => $country->cca2,
                            'cca3' => $country->cca3,
                            'capital' => $country->capital,
                            'region' => $country->region,
                            'latitude' => (float) $country->latitude,
                            'longitude' => (float) $country->longitude,
                            'flag_url' => $country->flag_url,
                        ],
                    ],
                    $weather
                ),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Data cuaca gagal diambil dari Open-Meteo.',

                'detail' => config('app.debug')
                    ? $exception->getMessage()
                    : null,
            ], 502);
        }
    }
}