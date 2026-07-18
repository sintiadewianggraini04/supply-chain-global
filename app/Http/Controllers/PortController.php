<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Contracts\View\View;

class PortController extends Controller
{
    public function index(): View
    {
        $countries = Port::query()
            ->whereNotNull('country_code')
            ->whereNotNull('country_name')
            ->select([
                'country_code',
                'country_name',
            ])
            ->distinct()
            ->orderBy('country_name')
            ->get();

        return view('ports.index', [
            'totalPorts' => Port::count(),

            'highRiskPorts' => Port::query()
                ->where('risk_level', 'high')
                ->count(),

            'mediumRiskPorts' => Port::query()
                ->where('risk_level', 'medium')
                ->count(),

            'lowRiskPorts' => Port::query()
                ->where('risk_level', 'low')
                ->count(),

            'countries' => $countries,
        ]);
    }
}