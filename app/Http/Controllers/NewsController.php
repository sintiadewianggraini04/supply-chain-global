<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\SentimentResult;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $categories = [
            'logistics' =>
                'Logistics',

            'trade' =>
                'Trade',

            'shipping' =>
                'Shipping',

            'economy' =>
                'Economy',

            'geopolitics' =>
                'Geopolitics',
        ];

        $totalNews =
            NewsArticle::query()->count();

        $sentimentCounts =
            SentimentResult::query()
                ->selectRaw(
                    '
                    sentiment_label,
                    COUNT(*) as total
                    '
                )
                ->groupBy(
                    'sentiment_label'
                )
                ->pluck(
                    'total',
                    'sentiment_label'
                );

        $positiveNews = (int) (
            $sentimentCounts['positive']
            ?? 0
        );

        $neutralNews = (int) (
            $sentimentCounts['neutral']
            ?? 0
        );

        $negativeNews = (int) (
            $sentimentCounts['negative']
            ?? 0
        );

        $analyzedNews =
            $positiveNews
            + $neutralNews
            + $negativeNews;

        $unanalyzedNews = max(
            0,
            $totalNews - $analyzedNews
        );

        return view(
            'news.index',
            [
                'totalNews' =>
                    $totalNews,

                'analyzedNews' =>
                    $analyzedNews,

                'unanalyzedNews' =>
                    $unanalyzedNews,

                'positiveNews' =>
                    $positiveNews,

                'neutralNews' =>
                    $neutralNews,

                'negativeNews' =>
                    $negativeNews,

                'positivePercentage' =>
                    $this->calculatePercentage(
                        $positiveNews,
                        $analyzedNews
                    ),

                'neutralPercentage' =>
                    $this->calculatePercentage(
                        $neutralNews,
                        $analyzedNews
                    ),

                'negativePercentage' =>
                    $this->calculatePercentage(
                        $negativeNews,
                        $analyzedNews
                    ),

                'categories' =>
                    $categories,

                'categoryCounts' =>
                    NewsArticle::query()
                        ->selectRaw(
                            '
                            category,
                            COUNT(*) as total
                            '
                        )
                        ->groupBy(
                            'category'
                        )
                        ->pluck(
                            'total',
                            'category'
                        ),

                'latestFetchedAt' =>
                    NewsArticle::query()
                        ->max('fetched_at'),
            ]
        );
    }

    private function calculatePercentage(
        int $count,
        int $total
    ): float {
        if ($total <= 0) {
            return 0;
        }

        return round(
            ($count / $total) * 100,
            2
        );
    }
}