<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $baseCurrency = strtoupper(
            (string) $request->input('base', 'USD')
        );

        $rates = ExchangeRate::query()
            ->where('base_currency', $baseCurrency)
            ->when($request->filled('target'), function ($query) use ($request) {
                $query->where(
                    'target_currency',
                    strtoupper((string) $request->input('target'))
                );
            })
            ->orderBy('target_currency')
            ->get([
                'base_currency',
                'target_currency',
                'rate',
                'rate_date',
                'provider',
                'fetched_at',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Currency rates retrieved successfully.',
            'data' => $rates,
            'meta' => [
                'base_currency' => $baseCurrency,
                'count' => $rates->count(),
            ],
        ]);
    }
}