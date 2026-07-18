<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsApiController extends Controller
{
    public function index(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'sentiment' => [
                'nullable',

                Rule::in([
                    'positive',
                    'neutral',
                    'negative',
                ]),
            ],

            'category' => [
                'nullable',

                Rule::in([
                    'logistics',
                    'trade',
                    'shipping',
                    'economy',
                    'geopolitics',
                    'supply_chain',
                    'custom',
                ]),
            ],

            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $limit = (int) (
            $validated['limit'] ?? 50
        );

        $query = NewsArticle::query()
            ->with('sentiment');

        if (
            ! empty(
                $validated['sentiment']
            )
        ) {
            $sentiment =
                $validated['sentiment'];

            $query->whereHas(
                'sentiment',
                function (
                    Builder $sentimentQuery
                ) use ($sentiment) {
                    $sentimentQuery->where(
                        'sentiment_label',
                        $sentiment
                    );
                }
            );
        }

        if (
            ! empty(
                $validated['category']
            )
        ) {
            $query->where(
                'category',
                $validated['category']
            );
        }

        if (
            ! empty(
                $validated['search']
            )
        ) {
            $search = trim(
                $validated['search']
            );

            $query->where(
                function (
                    Builder $searchQuery
                ) use ($search) {
                    $searchQuery
                        ->where(
                            'title',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'content',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'source_name',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        $filteredCount =
            (clone $query)->count();

        $positiveCount =
            $this->countBySentiment(
                clone $query,
                'positive'
            );

        $neutralCount =
            $this->countBySentiment(
                clone $query,
                'neutral'
            );

        $negativeCount =
            $this->countBySentiment(
                clone $query,
                'negative'
            );

        $analyzedCount =
            $positiveCount
            + $neutralCount
            + $negativeCount;

        $news = $query
            ->latest('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,

            'message' =>
                'News retrieved successfully.',

            'data' => $news,

            'meta' => [
                'count' =>
                    $news->count(),

                'filtered_count' =>
                    $filteredCount,

                'limit' =>
                    $limit,

                'sentiment_summary' => [
                    'analyzed_count' =>
                        $analyzedCount,

                    'positive' => [
                        'count' =>
                            $positiveCount,

                        'percentage' =>
                            $this
                                ->calculatePercentage(
                                    $positiveCount,
                                    $analyzedCount
                                ),
                    ],

                    'neutral' => [
                        'count' =>
                            $neutralCount,

                        'percentage' =>
                            $this
                                ->calculatePercentage(
                                    $neutralCount,
                                    $analyzedCount
                                ),
                    ],

                    'negative' => [
                        'count' =>
                            $negativeCount,

                        'percentage' =>
                            $this
                                ->calculatePercentage(
                                    $negativeCount,
                                    $analyzedCount
                                ),
                    ],
                ],
            ],
        ]);
    }

    private function countBySentiment(
        Builder $query,
        string $sentiment
    ): int {
        return $query
            ->whereHas(
                'sentiment',
                function (
                    Builder $sentimentQuery
                ) use ($sentiment) {
                    $sentimentQuery->where(
                        'sentiment_label',
                        $sentiment
                    );
                }
            )
            ->count();
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