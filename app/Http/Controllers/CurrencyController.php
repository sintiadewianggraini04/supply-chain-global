<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Contracts\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        return view('currency.index', [
            'latestRates' => ExchangeRate::query()
                ->where('base_currency', 'USD')
                ->orderBy('target_currency')
                ->get(),

            'totalRates' => ExchangeRate::count(),
        ]);
    }
}