<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\FavoriteCountry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteMonitoringController extends Controller
{
    public function index(): View
    {
        $favorites = FavoriteCountry::query()
            ->with('country')
            ->whereHas('country')
            ->latest()
            ->get();

        return view('favorites.index', [
            'favorites' => $favorites,
            'totalFavorites' => $favorites->count(),
        ]);
    }

    public function store(
        Request $request,
        Country $country
    ): JsonResponse|RedirectResponse {
        FavoriteCountry::firstOrCreate([
            'country_id' => $country->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' =>
                    "{$country->name} berhasil ditambahkan ke favorit.",
                'is_favorite' => true,
            ]);
        }

        return back()->with(
            'success',
            "{$country->name} berhasil ditambahkan ke favorit."
        );
    }

    public function destroy(
        Request $request,
        Country $country
    ): JsonResponse|RedirectResponse {
        FavoriteCountry::query()
            ->where('country_id', $country->id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' =>
                    "{$country->name} dihapus dari daftar favorit.",
                'is_favorite' => false,
            ]);
        }

        return back()->with(
            'success',
            "{$country->name} dihapus dari daftar favorit."
        );
    }
}