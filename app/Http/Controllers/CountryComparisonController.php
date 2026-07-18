<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Contracts\View\View;

class CountryComparisonController extends Controller
{
    public function index(): View
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->whereNotNull('cca3')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'cca2',
                'cca3',
                'flag_url',
                'currency_code',
            ]);

        $defaultCountryA =
            $countries
                ->firstWhere('cca3', 'DEU')
                ?->id
            ?? $countries->first()?->id;

        $defaultCountryB =
            $countries
                ->firstWhere('cca3', 'AUS')
                ?->id
            ?? $countries->skip(1)->first()?->id
            ?? $defaultCountryA;

        return view('comparison.index', [
            'countries' => $countries,

            'defaultCountryA' =>
                $defaultCountryA,

            'defaultCountryB' =>
                $defaultCountryB,
        ]);
    }
}