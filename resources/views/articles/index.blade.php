@extends('layouts.app')

@section(
    'title',
    'Artikel Analisis - Supply Chain Global'
)

@push('styles')
    <style>
        .article-hero {
            position: relative;
            overflow: hidden;
            padding: 32px;
            margin-bottom: 24px;
            border-radius: 20px;
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    #0f172a 0%,
                    #1d4ed8 100%
                );
            box-shadow:
                0 18px 45px
                rgba(15, 23, 42, 0.16);
        }

        .article-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -70px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background:
                rgba(255, 255, 255, 0.10);
        }

        .article-hero::after {
            content: '';
            position: absolute;
            right: 140px;
            bottom: -110px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background:
                rgba(255, 255, 255, 0.06);
        }

        .article-hero-content {
            position: relative;
            z-index: 2;
            max-width: 760px;
        }

        .article-hero h2 {
            margin-bottom: 10px;
            font-size: clamp(
                1.8rem,
                4vw,
                2.7rem
            );
            font-weight: 750;
        }

        .article-hero p {
            max-width: 680px;
            margin-bottom: 0;
            color: rgba(
                255,
                255,
                255,
                0.82
            );
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .article-filter-card {
            margin-bottom: 24px;
        }

        .article-grid {
            display: grid;
            grid-template-columns:
                repeat(
                    3,
                    minmax(0, 1fr)
                );
            gap: 22px;
        }

        .article-card {
            display: flex;
            flex-direction: column;
            min-height: 330px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
            box-shadow:
                0 10px 28px
                rgba(15, 23, 42, 0.06);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }

        .article-card:hover {
            transform: translateY(-4px);
            border-color: #bfdbfe;
            box-shadow:
                0 18px 38px
                rgba(15, 23, 42, 0.11);
        }

        .article-card-top {
            height: 8px;
            background:
                linear-gradient(
                    90deg,
                    #2563eb,
                    #38bdf8
                );
        }

        .article-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 24px;
        }

        .article-category {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            margin-bottom: 16px;
            padding: 6px 11px;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            color: #1d4ed8;
            background: #eff6ff;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .article-card-title {
            margin-bottom: 12px;
            color: #12233d;
            font-size: 1.18rem;
            font-weight: 750;
            line-height: 1.45;
        }

        .article-card-summary {
            flex: 1;
            margin-bottom: 20px;
            color: #64748b;
            line-height: 1.7;
        }

        .article-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px 14px;
            margin-bottom: 18px;
            color: #64748b;
            font-size: 0.82rem;
        }

        .article-read-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            color: #ffffff;
            background: #2563eb;
            font-weight: 700;
            text-decoration: none;
            transition:
                background 0.2s ease;
        }

        .article-read-link:hover {
            color: #ffffff;
            background: #1d4ed8;
        }

        .article-empty {
            padding: 60px 24px;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            text-align: center;
            background: #ffffff;
        }

        .article-empty h3 {
            color: #12233d;
        }

        .article-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 28px;
            padding: 18px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
        }

        @media (max-width: 1100px) {
            .article-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0, 1fr)
                    );
            }
        }

        @media (max-width: 700px) {
            .article-hero {
                padding: 25px;
            }

            .article-grid {
                grid-template-columns: 1fr;
            }

            .article-pagination {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <section class="article-hero">
        <div class="article-hero-content">
            <span
                class="badge text-bg-light mb-3"
            >
                Supply Chain Insights
            </span>

            <h2>Artikel Analisis</h2>

            <p>
                Baca analisis terbaru mengenai logistik,
                perdagangan, pelayaran, ekonomi,
                geopolitik, dan risiko rantai pasok
                yang diterbitkan administrator.
            </p>
        </div>
    </section>

    <section
        class="dashboard-card
        article-filter-card"
    >
        <form
            method="GET"
            action="{{ route('articles.index') }}"
        >
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label
                        for="articleSearch"
                        class="form-label"
                    >
                        Cari Artikel
                    </label>

                    <input
                        type="search"
                        id="articleSearch"
                        name="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Cari judul, kategori, atau isi artikel..."
                    >
                </div>

                <div class="col-lg-3">
                    <label
                        for="articleCategory"
                        class="form-label"
                    >
                        Kategori
                    </label>

                    <select
                        id="articleCategory"
                        name="category"
                        class="form-select"
                    >
                        <option value="">
                            Semua Kategori
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category }}"
                                @selected(
                                    $selectedCategory
                                    === $category
                                )
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3">
                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary flex-grow-1"
                        >
                            Tampilkan
                        </button>

                        <a
                            href="{{ route('articles.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <div
        class="d-flex justify-content-between
        align-items-center mb-3"
    >
        <div>
            <h3 class="mb-1">
                Artikel Terpublikasi
            </h3>

            <p class="text-secondary mb-0">
                Menampilkan
                {{ $articles->total() }}
                artikel.
            </p>
        </div>
    </div>

    @if ($articles->isEmpty())
        <section class="article-empty">
            <h3>Artikel belum tersedia</h3>

            <p class="text-secondary mb-0">
                Belum ada artikel terpublikasi
                yang sesuai dengan pencarian.
            </p>
        </section>
    @else
        <section class="article-grid">
            @foreach ($articles as $article)
                <article class="article-card">
                    <div class="article-card-top"></div>

                    <div class="article-card-body">
                        <span class="article-category">
                            {{ $article->category }}
                        </span>

                        <h3 class="article-card-title">
                            {{ $article->title }}
                        </h3>

                        <p class="article-card-summary">
                            {{
                                $article->summary
                                ?: \Illuminate\Support\Str::limit(
                                    $article->content,
                                    180
                                )
                            }}
                        </p>

                        <div class="article-card-meta">
                            <span>
                                {{
                                    $article->author?->name
                                    ?? 'Administrator'
                                }}
                            </span>

                            <span>
                                {{
                                    $article->published_at
                                        ?->format('d/m/Y H:i')
                                    ?? '-'
                                }}
                            </span>
                        </div>

                        <a
                            href="{{
                                route(
                                    'articles.show',
                                    $article->slug
                                )
                            }}"
                            class="article-read-link"
                        >
                            Baca Artikel
                        </a>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($articles->hasPages())
            <nav class="article-pagination">
                <div>
                    @if ($articles->onFirstPage())
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            disabled
                        >
                            Sebelumnya
                        </button>
                    @else
                        <a
                            href="{{ $articles->previousPageUrl() }}"
                            class="btn btn-outline-primary"
                        >
                            Sebelumnya
                        </a>
                    @endif
                </div>

                <div class="text-secondary">
                    Halaman
                    <strong>
                        {{ $articles->currentPage() }}
                    </strong>
                    dari
                    <strong>
                        {{ $articles->lastPage() }}
                    </strong>
                </div>

                <div>
                    @if ($articles->hasMorePages())
                        <a
                            href="{{ $articles->nextPageUrl() }}"
                            class="btn btn-primary"
                        >
                            Berikutnya
                        </a>
                    @else
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            disabled
                        >
                            Berikutnya
                        </button>
                    @endif
                </div>
            </nav>
        @endif
    @endif
@endsection