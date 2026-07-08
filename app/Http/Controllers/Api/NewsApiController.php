<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $news = NewsArticle::query()
            ->with('sentiment')
            ->when($request->filled('sentiment'), function ($query) use ($request) {
                $query->whereHas('sentiment', function ($sentimentQuery) use ($request) {
                    $sentimentQuery->where(
                        'sentiment_label',
                        $request->input('sentiment')
                    );
                });
            })
            ->latest('published_at')
            ->limit((int) $request->input('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'News retrieved successfully.',
            'data' => $news,
            'meta' => [
                'count' => $news->count(),
            ],
        ]);
    }
}