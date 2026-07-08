@extends('layouts.app')

@section('title', 'News Intelligence - Global Supply Chain')

@section('content')
    <div class="topbar">
        <div>
            <h2>News Intelligence</h2>

            <p>
                Monitoring berita global terkait logistics, trade, shipping,
                economy, dan supply chain menggunakan sentiment analysis.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                Total News: {{ $totalNews }}
            </span>
        </div>
    </div>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Total Articles</h3>
                <p class="metric-value">{{ $totalNews }}</p>
                <p class="metric-description">Berita tersimpan</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Positive News</h3>
                <p class="metric-value status-low">{{ $positiveNews }}</p>
                <p class="metric-description">Sentimen positif</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Neutral News</h3>
                <p class="metric-value status-medium">{{ $neutralNews }}</p>
                <p class="metric-description">Sentimen netral</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card">
                <h3>Negative News</h3>
                <p class="metric-value status-high">{{ $negativeNews }}</p>
                <p class="metric-description">Potensi risiko berita</p>
            </div>
        </div>
    </section>

    <section class="dashboard-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="sentimentFilter" class="form-label">
                    Filter sentiment
                </label>

                <select id="sentimentFilter" class="form-select">
                    <option value="">Semua sentiment</option>
                    <option value="positive">Positive</option>
                    <option value="neutral">Neutral</option>
                    <option value="negative">Negative</option>
                </select>
            </div>

            <div class="col-md-3">
                <button
                    type="button"
                    id="reloadNews"
                    class="btn btn-primary w-100"
                >
                    Reload Data
                </button>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-xl-5">
            <div class="dashboard-card">
                <h3 class="mb-1">Sentiment Distribution</h3>

                <p class="text-secondary">
                    Distribusi sentimen berita berdasarkan kamus kata positif dan negatif.
                </p>

                <div class="chart-container">
                    <canvas id="sentimentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="dashboard-card">
                <h3 class="mb-1">Latest Supply Chain News</h3>

                <p class="text-secondary">
                    Berita terbaru yang tersimpan dari GNews API.
                </p>

                <div id="newsLoading" class="alert alert-info">
                    Mengambil data berita...
                </div>

                <div id="newsList" class="d-flex flex-column gap-3"></div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const sentimentFilter = document.getElementById('sentimentFilter');
        const reloadButton = document.getElementById('reloadNews');
        const loadingBox = document.getElementById('newsLoading');
        const newsList = document.getElementById('newsList');

        let sentimentChart = null;

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function sentimentBadge(label) {
            if (label === 'positive') {
                return '<span class="badge text-bg-success">Positive</span>';
            }

            if (label === 'negative') {
                return '<span class="badge text-bg-danger">Negative</span>';
            }

            return '<span class="badge text-bg-warning">Neutral</span>';
        }

        function formatDate(value) {
            if (! value) {
                return '-';
            }

            return new Date(value).toLocaleString('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short'
            });
        }

        async function loadNews() {
            loadingBox.classList.remove('d-none');
            newsList.innerHTML = '';

            const params = new URLSearchParams();
            params.append('limit', '20');

            if (sentimentFilter.value !== '') {
                params.append('sentiment', sentimentFilter.value);
            }

            const response = await fetch(`/api/news?${params.toString()}`);
            const result = await response.json();

            loadingBox.classList.add('d-none');

            if (! result.success || result.data.length === 0) {
                newsList.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        Data berita belum tersedia. Jalankan php artisan news:sync.
                    </div>
                `;

                renderSentimentChart([]);

                return;
            }

            newsList.innerHTML = result.data.map((article) => {
                const sentiment = article.sentiment;
                const label = sentiment?.sentiment_label ?? 'neutral';

                const positiveWords = sentiment?.matched_positive_words ?? [];
                const negativeWords = sentiment?.matched_negative_words ?? [];

                return `
                    <article class="border rounded-3 p-3">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div>
                                <h5 class="mb-1">
                                    ${escapeHtml(article.title)}
                                </h5>

                                <div class="text-secondary small">
                                    ${escapeHtml(article.source_name ?? '-')}
                                    ·
                                    ${formatDate(article.published_at)}
                                </div>
                            </div>

                            <div>
                                ${sentimentBadge(label)}
                            </div>
                        </div>

                        <p class="mb-2">
                            ${escapeHtml(article.description ?? '-')}
                        </p>

                        <div class="small text-secondary mb-2">
                            Positive score: ${sentiment?.positive_score ?? 0}
                            ·
                            Negative score: ${sentiment?.negative_score ?? 0}
                            ·
                            Neutral score: ${sentiment?.neutral_score ?? 0}
                        </div>

                        <a
                            href="${escapeHtml(article.url)}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Open Article
                        </a>
                    </article>
                `;
            }).join('');

            renderSentimentChart(result.data);
        }

        function renderSentimentChart(news) {
            const chartElement = document.getElementById('sentimentChart');

            const sentimentCounts = {
                positive: 0,
                neutral: 0,
                negative: 0,
            };

            news.forEach((article) => {
                const label = article.sentiment?.sentiment_label ?? 'neutral';

                if (sentimentCounts[label] !== undefined) {
                    sentimentCounts[label]++;
                }
            });

            if (sentimentChart) {
                sentimentChart.destroy();
            }

            sentimentChart = new window.Chart(chartElement, {
                type: 'doughnut',

                data: {
                    labels: ['Positive', 'Neutral', 'Negative'],
                    datasets: [
                        {
                            data: [
                                sentimentCounts.positive,
                                sentimentCounts.neutral,
                                sentimentCounts.negative,
                            ],
                        }
                    ]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        sentimentFilter.addEventListener('change', loadNews);
        reloadButton.addEventListener('click', loadNews);

        loadNews();
    </script>
@endpush