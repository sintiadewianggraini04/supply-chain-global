<?php

namespace App\Services;

use App\Models\NewsArticle;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GNewsService
{
    private const CATEGORY_QUERIES = [
        'logistics' =>
            '"supply chain" OR logistics OR freight',

        'trade' =>
            '"international trade" OR imports OR exports',

        'shipping' =>
            'shipping OR maritime OR "container port"',

        'economy' =>
            'economy OR inflation OR GDP',

        'geopolitics' =>
            'geopolitics OR sanctions OR "trade war"',
    ];

    public function __construct(
        private readonly NewsSentimentService $sentimentService
    ) {
    }

    public function sync(
        ?string $query = null,
        string $category = 'custom'
    ): array {
        $query = $query !== null
            ? trim($query)
            : null;

        if ($query !== null && $query !== '') {
            $requests = [
                $this->normalizeCategory($category) => $query,
            ];
        } else {
            $requests = self::CATEGORY_QUERIES;
        }

        $result = [
            'processed' => 0,
            'categories' => [],
            'errors' => [],
        ];

        /*
         * Digunakan agar URL yang sama dari beberapa kategori
         * tidak diproses dua kali dalam satu sinkronisasi.
         */
        $seenUrls = [];

        foreach ($requests as $categoryName => $categoryQuery) {
            try {
                $processed = $this->syncCategory(
                    $categoryName,
                    $categoryQuery,
                    $seenUrls
                );

                $result['categories'][$categoryName] =
                    $processed;

                $result['processed'] += $processed;
            } catch (Throwable $exception) {
                report($exception);

                $result['errors'][$categoryName] =
                    $exception->getMessage();
            }
        }

        if (
            $result['processed'] === 0
            && $result['errors'] !== []
        ) {
            throw new RuntimeException(
                'Semua permintaan ke GNews API gagal.'
            );
        }

        return $result;
    }

    private function syncCategory(
        string $category,
        string $query,
        array &$seenUrls
    ): int {
        $baseUrl = trim(
            (string) config('services.gnews.base_url')
        );

        $apiKey = trim(
            (string) config('services.gnews.api_key')
        );

        if ($baseUrl === '') {
            throw new RuntimeException(
                'GNEWS_BASE_URL belum dikonfigurasi.'
            );
        }

        if ($apiKey === '') {
            throw new RuntimeException(
                'GNEWS_API_KEY belum diisi di file .env.'
            );
        }

        $response = Http::acceptJson()
            ->withHeaders([
                'X-Api-Key' => $apiKey,
            ])
            ->timeout(60)
            ->retry(3, 1000)
            ->get($baseUrl, [
                'q' => $query,
                'lang' => 'en',
                'max' => 10,
                'page' => 1,
                'in' => 'title,description',
                'sortby' => 'publishedAt',
                'nullable' => 'description,content,image',
            ]);

        $response->throw();

        $articles = $response->json('articles', []);

        if (! is_array($articles)) {
            throw new RuntimeException(
                'Format respons GNews tidak sesuai.'
            );
        }

        $processedArticles = 0;

        foreach ($articles as $articleData) {
            $url = trim(
                (string) ($articleData['url'] ?? '')
            );

            if ($url === '') {
                continue;
            }

            /*
             * Artikel yang sama kadang muncul pada dua kategori.
             * Artikel tersebut hanya diproses sekali.
             */
            if (isset($seenUrls[$url])) {
                continue;
            }

            $seenUrls[$url] = true;

            $article = NewsArticle::firstOrNew([
                'url' => $url,
            ]);

            $isNewArticle = ! $article->exists;

            $article->fill([
                'title' =>
                    $articleData['title']
                    ?? 'Untitled News',

                'description' =>
                    $articleData['description']
                    ?? null,

                'content' =>
                    $articleData['content']
                    ?? null,

                'source_name' =>
                    data_get(
                        $articleData,
                        'source.name'
                    ),

                'image_url' =>
                    $articleData['image']
                    ?? null,

                'published_at' =>
                    $articleData['publishedAt']
                    ?? null,

                'fetched_at' => now(),

                'raw_response' => $articleData,
            ]);

            /*
             * Data lama sebelumnya memakai kategori
             * supply_chain. Saat ditemukan kembali,
             * kategori tersebut diperbarui.
             */
            if (
                $isNewArticle
                || $article->category === null
                || $article->category === 'supply_chain'
            ) {
                $article->category = $category;
            }

            $article->save();

            $this->sentimentService->analyze(
                $article
            );

            $processedArticles++;
        }

        return $processedArticles;
    }

    private function normalizeCategory(
        string $category
    ): string {
        $category = strtolower(
            trim($category)
        );

        $allowedCategories = [
            'logistics',
            'trade',
            'shipping',
            'economy',
            'geopolitics',
            'custom',
        ];

        if (! in_array(
            $category,
            $allowedCategories,
            true
        )) {
            return 'custom';
        }

        return $category;
    }
}