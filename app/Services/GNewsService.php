<?php

namespace App\Services;

use App\Models\NewsArticle;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GNewsService
{
    public function __construct(
        private readonly NewsSentimentService $sentimentService
    ) {
    }

    public function sync(?string $query = null): int
    {
        $baseUrl = (string) config('services.gnews.base_url');
        $apiKey = (string) config('services.gnews.api_key');

        if ($apiKey === '') {
            throw new RuntimeException(
                'GNEWS_API_KEY belum diisi di file .env.'
            );
        }

        $query ??= (string) config('services.gnews.default_query');

        $response = Http::acceptJson()
            ->timeout(60)
            ->retry(3, 1000)
            ->get($baseUrl, [
                'q' => $query,
                'lang' => 'en',
                'max' => 10,
                'sortby' => 'publishedAt',
                'apikey' => $apiKey,
            ]);

        $response->throw();

        $articles = $response->json('articles', []);

        if (! is_array($articles)) {
            throw new RuntimeException(
                'Format response GNews tidak sesuai.'
            );
        }

        $savedArticles = 0;

        foreach ($articles as $articleData) {
            $url = $articleData['url'] ?? null;

            if (! $url) {
                continue;
            }

            $article = NewsArticle::updateOrCreate(
                [
                    'url' => $url,
                ],
                [
                    'title' => $articleData['title'] ?? 'Untitled News',
                    'description' => $articleData['description'] ?? null,
                    'content' => $articleData['content'] ?? null,
                    'source_name' => data_get($articleData, 'source.name'),
                    'image_url' => $articleData['image'] ?? null,
                    'category' => 'supply_chain',
                    'published_at' => $articleData['publishedAt'] ?? null,
                    'fetched_at' => now(),
                    'raw_response' => $articleData,
                ]
            );

            $this->sentimentService->analyze($article);

            $savedArticles++;
        }

        return $savedArticles;
    }
}