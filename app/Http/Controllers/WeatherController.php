<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Contracts\View\View;

class WeatherController extends Controller
{
    public function index(): View
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'cca2',
                'capital',
                'latitude',
                'longitude',
                'flag_url',
            ]);

        $defaultCountryId = $countries
            ->firstWhere('cca2', 'ID')
            ?->id ?? $countries->first()?->id;

        return view('weather.index', [
            'countries' => $countries,
            'totalCountries' => $countries->count(),
            'defaultCountryId' => $defaultCountryId,
        ]);
    }
}