@extends('layouts.app')

@section(
    'title',
    $article->title . ' - Supply Chain Global'
)

@push('styles')
    <style>
        .article-detail-wrapper {
            max-width: 980px;
            margin: 0 auto;
        }

        .article-detail-header {
            padding: 34px;
            margin-bottom: 24px;
            border-radius: 20px;
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1d4ed8
                );
            box-shadow:
                0 18px 45px
                rgba(15, 23, 42, 0.16);
        }

        .article-detail-category {
            display: inline-flex;
            padding: 7px 12px;
            margin-bottom: 18px;
            border: 1px solid
                rgba(255, 255, 255, 0.28);
            border-radius: 999px;
            color: #ffffff;
            background:
                rgba(255, 255, 255, 0.13);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .article-detail-title {
            max-width: 850px;
            margin-bottom: 18px;
            font-size: clamp(
                2rem,
                5vw,
                3.2rem
            );
            font-weight: 780;
            line-height: 1.22;
        }

        .article-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 18px;
            color:
                rgba(
                    255,
                    255,
                    255,
                    0.78
                );
            font-size: 0.9rem;
        }

        .article-detail-card {
            padding: 34px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow:
                0 12px 34px
                rgba(15, 23, 42, 0.07);
        }

        .article-summary-box {
            padding: 20px 22px;
            margin-bottom: 30px;
            border-left: 5px solid #2563eb;
            border-radius: 0 12px 12px 0;
            color: #334155;
            background: #eff6ff;
            font-size: 1.05rem;
            line-height: 1.75;
        }

        .article-content {
            color: #334155;
            font-size: 1.04rem;
            line-height: 1.9;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .related-grid {
            display: grid;
            grid-template-columns:
                repeat(
                    3,
                    minmax(0, 1fr)
                );
            gap: 18px;
        }

        .related-card {
            display: flex;
            flex-direction: column;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            color: inherit;
            background: #ffffff;
            text-decoration: none;
            transition:
                transform 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .related-card:hover {
            color: inherit;
            transform: translateY(-3px);
            border-color: #bfdbfe;
            box-shadow:
                0 14px 30px
                rgba(15, 23, 42, 0.08);
        }

        .related-card-category {
            margin-bottom: 10px;
            color: #2563eb;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .related-card-title {
            margin-bottom: 12px;
            color: #12233d;
            font-size: 1rem;
            font-weight: 750;
            line-height: 1.45;
        }

        .related-card-date {
            margin-top: auto;
            color: #64748b;
            font-size: 0.8rem;
        }

        @media (max-width: 800px) {
            .article-detail-header,
            .article-detail-card {
                padding: 25px;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="article-detail-wrapper">
        <div class="mb-3">
            <a
                href="{{ route('articles.index') }}"
                class="btn btn-outline-secondary"
            >
                ← Kembali ke Artikel
            </a>
        </div>

        <header class="article-detail-header">
            <span class="article-detail-category">
                {{ $article->category }}
            </span>

            <h1 class="article-detail-title">
                {{ $article->title }}
            </h1>

            <div class="article-detail-meta">
                <span>
                    Penulis:
                    {{
                        $article->author?->name
                        ?? 'Administrator'
                    }}
                </span>

                <span>
                    Dipublikasikan:
                    {{
                        $article->published_at
                            ?->format('d/m/Y H:i')
                        ?? '-'
                    }}
                </span>
            </div>
        </header>

        <article class="article-detail-card">
            @if ($article->summary)
                <div class="article-summary-box">
                    {{ $article->summary }}
                </div>
            @endif

            <div class="article-content">{{ $article->content }}</div>
        </article>

        @if ($relatedArticles->isNotEmpty())
            <section class="dashboard-card">
                <div class="mb-4">
                    <h3 class="mb-1">
                        Artikel Terkait
                    </h3>

                    <p class="text-secondary mb-0">
                        Artikel lain dalam kategori
                        {{ $article->category }}.
                    </p>
                </div>

                <div class="related-grid">
                    @foreach ($relatedArticles as $relatedArticle)
                        <a
                            href="{{
                                route(
                                    'articles.show',
                                    $relatedArticle->slug
                                )
                            }}"
                            class="related-card"
                        >
                            <span
                                class="related-card-category"
                            >
                                {{ $relatedArticle->category }}
                            </span>

                            <h4 class="related-card-title">
                                {{ $relatedArticle->title }}
                            </h4>

                            <span class="related-card-date">
                                {{
                                    $relatedArticle
                                        ->published_at
                                        ?->format('d/m/Y')
                                    ?? '-'
                                }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection