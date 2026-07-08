<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'totalNews' => NewsArticle::count(),

            'positiveNews' => NewsArticle::whereHas('sentiment', function ($query) {
                $query->where('sentiment_label', 'positive');
            })->count(),

            'negativeNews' => NewsArticle::whereHas('sentiment', function ($query) {
                $query->where('sentiment_label', 'negative');
            })->count(),

            'neutralNews' => NewsArticle::whereHas('sentiment', function ($query) {
                $query->where('sentiment_label', 'neutral');
            })->count(),
        ]);
    }
}