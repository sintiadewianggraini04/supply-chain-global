<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use Illuminate\Contracts\View\View;

class CurrencyController extends Controller
{
    public function index(
        ExchangeRateService $exchangeRateService
    ): View {
        $currencies = $exchangeRateService
            ->currencyCodes();

        return view('currency.index', [
            'currencies' => $currencies,

            'defaultBase' => 'USD',

            'defaultTarget' =>
                $currencies->contains('IDR')
                    ? 'IDR'
                    : $currencies->first(),
        ]);
    }
}