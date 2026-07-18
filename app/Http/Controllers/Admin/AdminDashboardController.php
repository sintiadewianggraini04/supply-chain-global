<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalysisArticle;
use App\Models\Port;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' =>
                User::query()->count(),

            'activeUsers' =>
                User::query()
                    ->where('is_active', true)
                    ->count(),

            'adminUsers' =>
                User::query()
                    ->where('role', 'admin')
                    ->count(),

            'totalPorts' =>
                Port::query()->count(),

            'totalArticles' =>
                AnalysisArticle::query()
                    ->count(),

            'publishedArticles' =>
                AnalysisArticle::query()
                    ->where(
                        'status',
                        'published'
                    )
                    ->count(),

            'draftArticles' =>
                AnalysisArticle::query()
                    ->where(
                        'status',
                        'draft'
                    )
                    ->count(),

            'recentUsers' =>
                User::query()
                    ->latest()
                    ->limit(5)
                    ->get(),

            'recentPorts' =>
                Port::query()
                    ->latest()
                    ->limit(5)
                    ->get(),
        ]);
    }
}