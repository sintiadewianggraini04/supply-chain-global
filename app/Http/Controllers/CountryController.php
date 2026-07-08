<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Contracts\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $totalCountries = Country::count();

        $regions = Country::query()
            ->whereNotNull('region')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view('countries.index', [
            'totalCountries' => $totalCountries,
            'regions' => $regions,
        ]);
    }
}