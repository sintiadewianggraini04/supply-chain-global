<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Contracts\View\View;

class PortController extends Controller
{
    public function index(): View
    {
        return view('ports.index', [
            'totalPorts' => Port::count(),
            'highRiskPorts' => Port::where('risk_level', 'high')->count(),
            'mediumRiskPorts' => Port::where('risk_level', 'medium')->count(),
            'lowRiskPorts' => Port::where('risk_level', 'low')->count(),
        ]);
    }
}