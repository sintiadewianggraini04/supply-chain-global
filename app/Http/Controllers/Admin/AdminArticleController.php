<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalysisArticle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminArticleController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input('search')
        );

        $articles = AnalysisArticle::query()
            ->with('author')
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
                                    'category',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $editingArticle = null;

        if ($request->filled('edit')) {
            $editingArticle =
                AnalysisArticle::query()
                    ->find(
                        $request->integer('edit')
                    );
        }

        return view(
            'admin.articles.index',
            [
                'articles' => $articles,
                'editingArticle' =>
                    $editingArticle,
                'search' => $search,
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validateArticle(
            $request
        );

        AnalysisArticle::query()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $this->createUniqueSlug(
                $validated['title']
            ),
            'category' =>
                $validated['category'],
            'summary' =>
                $validated['summary'] ?? null,
            'content' => $validated['content'],
            'status' => $validated['status'],
            'published_at' =>
                $validated['status']
                    === 'published'
                        ? now()
                        : null,
        ]);

        return redirect()
            ->route('admin.articles.index')
            ->with(
                'success',
                'Artikel analisis berhasil ditambahkan.'
            );
    }

    public function update(
        Request $request,
        AnalysisArticle $article
    ): RedirectResponse {
        $validated = $this->validateArticle(
            $request
        );

        $article->title =
            $validated['title'];

        $article->slug =
            $this->createUniqueSlug(
                $validated['title'],
                $article->id
            );

        $article->category =
            $validated['category'];

        $article->summary =
            $validated['summary'] ?? null;

        $article->content =
            $validated['content'];

        $article->status =
            $validated['status'];

        if ($validated['status'] === 'published') {
            $article->published_at ??= now();
        } else {
            $article->published_at = null;
        }

        $article->save();

        return redirect()
            ->route('admin.articles.index')
            ->with(
                'success',
                'Artikel analisis berhasil diperbarui.'
            );
    }

    public function destroy(
        AnalysisArticle $article
    ): RedirectResponse {
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with(
                'success',
                'Artikel analisis berhasil dihapus.'
            );
    }

    private function validateArticle(
        Request $request
    ): array {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',

                Rule::in([
                    'Logistics',
                    'Trade',
                    'Shipping',
                    'Economy',
                    'Geopolitics',
                    'Risk Analysis',
                ]),
            ],

            'summary' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'content' => [
                'required',
                'string',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],
        ]);
    }

    private function createUniqueSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'article';
        }

        $slug = $baseSlug;
        $number = 2;

        while (
            AnalysisArticle::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) =>
                        $query->where(
                            'id',
                            '!=',
                            $ignoreId
                        )
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$number}";
            $number++;
        }

        return $slug;
    }
}