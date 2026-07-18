<?php

namespace App\Http\Controllers;

use App\Models\AnalysisArticle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $category = trim(
            (string) $request->query(
                'category',
                ''
            )
        );

        $articles = AnalysisArticle::query()
            ->with('author')
            ->where(
                'status',
                'published'
            )
            ->whereNotNull(
                'published_at'
            )
            ->where(
                'published_at',
                '<=',
                now()
            )
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'title',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'summary',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'content',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'category',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $category !== '',
                fn ($query) =>
                    $query->where(
                        'category',
                        $category
                    )
            )
            ->orderByDesc(
                'published_at'
            )
            ->paginate(9)
            ->withQueryString();

        $categories = AnalysisArticle::query()
            ->where(
                'status',
                'published'
            )
            ->whereNotNull(
                'published_at'
            )
            ->where(
                'published_at',
                '<=',
                now()
            )
            ->whereNotNull(
                'category'
            )
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view(
            'articles.index',
            [
                'articles' => $articles,
                'categories' => $categories,
                'search' => $search,
                'selectedCategory' => $category,
            ]
        );
    }

    public function show(
        string $slug
    ): View {
        /*
         * Query langsung berdasarkan slug dan status.
         * Artikel draft akan menghasilkan halaman 404.
         */
        $article = AnalysisArticle::query()
            ->with('author')
            ->where(
                'slug',
                $slug
            )
            ->where(
                'status',
                'published'
            )
            ->whereNotNull(
                'published_at'
            )
            ->where(
                'published_at',
                '<=',
                now()
            )
            ->firstOrFail();

        $relatedArticles =
            AnalysisArticle::query()
                ->with('author')
                ->where(
                    'status',
                    'published'
                )
                ->whereNotNull(
                    'published_at'
                )
                ->where(
                    'published_at',
                    '<=',
                    now()
                )
                ->where(
                    'id',
                    '!=',
                    $article->id
                )
                ->where(
                    'category',
                    $article->category
                )
                ->orderByDesc(
                    'published_at'
                )
                ->limit(3)
                ->get();

        return view(
            'articles.show',
            [
                'article' => $article,
                'relatedArticles' =>
                    $relatedArticles,
            ]
        );
    }
}