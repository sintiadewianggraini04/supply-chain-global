@extends('layouts.app')

@section(
    'title',
    'News Intelligence - Global Supply Chain'
)

@push('styles')
    <style>
        .sentiment-percentage {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 8px;
        }

        .sentiment-percentage strong {
            font-size: 2rem;
            line-height: 1;
        }

        .sentiment-percentage span {
            color: #64748b;
            font-size: 0.9rem;
        }

        .sentiment-summary-grid {
            display: grid;
            grid-template-columns:
                repeat(
                    3,
                    minmax(0, 1fr)
                );
            gap: 14px;
            margin-bottom: 22px;
        }

        .sentiment-summary-item {
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            background: #f8fafc;
            text-align: center;
        }

        .sentiment-summary-item strong {
            display: block;
            margin-bottom: 5px;
            color: #0f172a;
            font-size: 1.45rem;
        }

        .sentiment-summary-item span {
            color: #64748b;
            font-size: 0.82rem;
        }

        .matched-word-section {
            padding: 12px 14px;
            margin-bottom: 14px;
            border-radius: 10px;
            background: #f8fafc;
        }

        .matched-word-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
        }

        .matched-word {
            display: inline-flex;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .matched-word-positive {
            color: #166534;
            background: #dcfce7;
        }

        .matched-word-negative {
            color: #991b1b;
            background: #fee2e2;
        }

        @media (max-width: 700px) {
            .sentiment-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="topbar">
        <div>
            <h2>News Intelligence</h2>

            <p>
                Monitoring berita global terkait
                logistics, trade, shipping, economy,
                dan geopolitics menggunakan
                lexicon based sentiment analysis.
            </p>
        </div>

        <div>
            <span
                id="newsResultBadge"
                class="badge text-bg-primary fs-6"
            >
                Total News: {{ $totalNews }}
            </span>
        </div>
    </div>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <h3>Total Articles</h3>

                <p class="metric-value">
                    {{ $totalNews }}
                </p>

                <p class="metric-description">
                    {{ $analyzedNews }}
                    berita sudah dianalisis.

                    @if ($unanalyzedNews > 0)
                        {{ $unanalyzedNews }}
                        berita belum dianalisis.
                    @endif
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <h3>Positive News</h3>

                <div class="sentiment-percentage">
                    <strong class="status-low">
                        {{
                            number_format(
                                $positivePercentage,
                                2,
                                ',',
                                '.'
                            )
                        }}%
                    </strong>

                    <span>
                        {{ $positiveNews }} berita
                    </span>
                </div>

                <p class="metric-description mb-0">
                    Sentimen positif berdasarkan
                    kamus kata positif.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <h3>Neutral News</h3>

                <div class="sentiment-percentage">
                    <strong class="status-medium">
                        {{
                            number_format(
                                $neutralPercentage,
                                2,
                                ',',
                                '.'
                            )
                        }}%
                    </strong>

                    <span>
                        {{ $neutralNews }} berita
                    </span>
                </div>

                <p class="metric-description mb-0">
                    Skor positif dan negatif
                    memiliki nilai yang sama.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <h3>Negative News</h3>

                <div class="sentiment-percentage">
                    <strong class="status-high">
                        {{
                            number_format(
                                $negativePercentage,
                                2,
                                ',',
                                '.'
                            )
                        }}%
                    </strong>

                    <span>
                        {{ $negativeNews }} berita
                    </span>
                </div>

                <p class="metric-description mb-0">
                    Sentimen negatif dan potensi
                    risiko berita.
                </p>
            </div>
        </div>
    </section>

    <section class="dashboard-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label
                    for="categoryFilter"
                    class="form-label"
                >
                    Kategori berita
                </label>

                <select
                    id="categoryFilter"
                    class="form-select"
                >
                    <option value="">
                        Semua kategori
                    </option>

                    @foreach (
                        $categories
                        as $value => $label
                    )
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label
                    for="sentimentFilter"
                    class="form-label"
                >
                    Filter sentiment
                </label>

                <select
                    id="sentimentFilter"
                    class="form-select"
                >
                    <option value="">
                        Semua sentiment
                    </option>

                    <option value="positive">
                        Positive
                    </option>

                    <option value="neutral">
                        Neutral
                    </option>

                    <option value="negative">
                        Negative
                    </option>
                </select>
            </div>

            <div class="col-md-4">
                <label
                    for="newsSearch"
                    class="form-label"
                >
                    Cari berita
                </label>

                <input
                    type="text"
                    id="newsSearch"
                    class="form-control"
                    placeholder="
                        Judul, deskripsi,
                        isi, atau sumber...
                    "
                >
            </div>

            <div class="col-md-2">
                <button
                    type="button"
                    id="reloadNews"
                    class="btn btn-primary w-100"
                >
                    Reload List
                </button>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-xl-5">
            <div class="dashboard-card h-100">
                <h3 class="mb-1">
                    Sentiment Distribution
                </h3>

                <p class="text-secondary">
                    Persentase berdasarkan berita
                    yang sesuai dengan filter.
                </p>

                <div class="sentiment-summary-grid">
                    <div class="sentiment-summary-item">
                        <strong
                            id="filteredPositivePercentage"
                            class="status-low"
                        >
                            0%
                        </strong>

                        <span id="filteredPositiveCount">
                            Positive: 0 berita
                        </span>
                    </div>

                    <div class="sentiment-summary-item">
                        <strong
                            id="filteredNeutralPercentage"
                            class="status-medium"
                        >
                            0%
                        </strong>

                        <span id="filteredNeutralCount">
                            Neutral: 0 berita
                        </span>
                    </div>

                    <div class="sentiment-summary-item">
                        <strong
                            id="filteredNegativePercentage"
                            class="status-high"
                        >
                            0%
                        </strong>

                        <span id="filteredNegativeCount">
                            Negative: 0 berita
                        </span>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="sentimentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="dashboard-card">
                <h3 class="mb-1">
                    Latest Global News
                </h3>

                <p class="text-secondary">
                    Maksimal 50 berita terbaru
                    dari database.
                </p>

                <div
                    id="newsLoading"
                    class="alert alert-info"
                >
                    Mengambil data berita...
                </div>

                <div
                    id="newsError"
                    class="alert alert-danger d-none"
                ></div>

                <div
                    id="newsList"
                    class="d-flex flex-column gap-3"
                ></div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module">
        const newsApiUrl =
            @json(route('api.news.index'));

        const categoryLabels =
            @json($categories);

        const categoryFilter =
            document.getElementById(
                'categoryFilter'
            );

        const sentimentFilter =
            document.getElementById(
                'sentimentFilter'
            );

        const searchInput =
            document.getElementById(
                'newsSearch'
            );

        const reloadButton =
            document.getElementById(
                'reloadNews'
            );

        const loadingBox =
            document.getElementById(
                'newsLoading'
            );

        const errorBox =
            document.getElementById(
                'newsError'
            );

        const newsList =
            document.getElementById(
                'newsList'
            );

        const resultBadge =
            document.getElementById(
                'newsResultBadge'
            );

        const positivePercentageElement =
            document.getElementById(
                'filteredPositivePercentage'
            );

        const neutralPercentageElement =
            document.getElementById(
                'filteredNeutralPercentage'
            );

        const negativePercentageElement =
            document.getElementById(
                'filteredNegativePercentage'
            );

        const positiveCountElement =
            document.getElementById(
                'filteredPositiveCount'
            );

        const neutralCountElement =
            document.getElementById(
                'filteredNeutralCount'
            );

        const negativeCountElement =
            document.getElementById(
                'filteredNegativeCount'
            );

        let sentimentChart = null;
        let searchTimer = null;

        function escapeHtml(value) {
            if (
                value === null
                || value === undefined
            ) {
                return '';
            }

            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function safeUrl(value) {
            try {
                const url = new URL(value);

                if (
                    url.protocol === 'http:'
                    || url.protocol === 'https:'
                ) {
                    return url.href;
                }
            } catch (error) {
                return '#';
            }

            return '#';
        }

        function categoryLabel(category) {
            return categoryLabels[category]
                ?? category
                    ?.replaceAll('_', ' ')
                    ?.replace(
                        /\b\w/g,
                        (character) =>
                            character.toUpperCase()
                    )
                ?? 'Uncategorized';
        }

        function categoryBadge(category) {
            return `
                <span class="badge text-bg-primary">
                    ${escapeHtml(
                        categoryLabel(category)
                    )}
                </span>
            `;
        }

        function sentimentBadge(label) {
            if (label === 'positive') {
                return `
                    <span class="badge text-bg-success">
                        Positive
                    </span>
                `;
            }

            if (label === 'negative') {
                return `
                    <span class="badge text-bg-danger">
                        Negative
                    </span>
                `;
            }

            return `
                <span class="badge text-bg-warning">
                    Neutral
                </span>
            `;
        }

        function formatDate(value) {
            if (! value) {
                return '-';
            }

            return new Date(value)
                .toLocaleString(
                    'id-ID',
                    {
                        dateStyle: 'medium',
                        timeStyle: 'short',
                    }
                );
        }

        function formatPercentage(value) {
            const number = Number(value ?? 0);

            return number.toLocaleString(
                'id-ID',
                {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }
            ) + '%';
        }

        function normalizeWordArray(value) {
            if (Array.isArray(value)) {
                return value;
            }

            if (typeof value === 'string') {
                try {
                    const parsed =
                        JSON.parse(value);

                    return Array.isArray(parsed)
                        ? parsed
                        : [];
                } catch (error) {
                    return [];
                }
            }

            return [];
        }

        function renderMatchedWords(
            positiveWords,
            negativeWords
        ) {
            const positiveHtml =
                positiveWords.length > 0
                    ? positiveWords
                        .map(
                            (word) => `
                                <span
                                    class="
                                        matched-word
                                        matched-word-positive
                                    "
                                >
                                    ${escapeHtml(word)}
                                </span>
                            `
                        )
                        .join('')
                    : `
                        <span class="text-secondary small">
                            Tidak ada
                        </span>
                    `;

            const negativeHtml =
                negativeWords.length > 0
                    ? negativeWords
                        .map(
                            (word) => `
                                <span
                                    class="
                                        matched-word
                                        matched-word-negative
                                    "
                                >
                                    ${escapeHtml(word)}
                                </span>
                            `
                        )
                        .join('')
                    : `
                        <span class="text-secondary small">
                            Tidak ada
                        </span>
                    `;

            return `
                <div class="matched-word-section">
                    <div class="small fw-semibold">
                        Positive words
                    </div>

                    <div class="matched-word-row">
                        ${positiveHtml}
                    </div>

                    <div class="small fw-semibold mt-3">
                        Negative words
                    </div>

                    <div class="matched-word-row">
                        ${negativeHtml}
                    </div>
                </div>
            `;
        }

        function setLoading(isLoading) {
            loadingBox.classList.toggle(
                'd-none',
                ! isLoading
            );

            reloadButton.disabled =
                isLoading;
        }

        function showError(message) {
            errorBox.textContent = message;

            errorBox.classList.remove(
                'd-none'
            );
        }

        function hideError() {
            errorBox.textContent = '';

            errorBox.classList.add(
                'd-none'
            );
        }

        function emptySentimentSummary() {
            return {
                analyzed_count: 0,

                positive: {
                    count: 0,
                    percentage: 0,
                },

                neutral: {
                    count: 0,
                    percentage: 0,
                },

                negative: {
                    count: 0,
                    percentage: 0,
                },
            };
        }

        function updateSentimentSummary(summary) {
            const data =
                summary
                ?? emptySentimentSummary();

            positivePercentageElement.textContent =
                formatPercentage(
                    data.positive?.percentage
                );

            neutralPercentageElement.textContent =
                formatPercentage(
                    data.neutral?.percentage
                );

            negativePercentageElement.textContent =
                formatPercentage(
                    data.negative?.percentage
                );

            positiveCountElement.textContent =
                `Positive: ${
                    data.positive?.count ?? 0
                } berita`;

            neutralCountElement.textContent =
                `Neutral: ${
                    data.neutral?.count ?? 0
                } berita`;

            negativeCountElement.textContent =
                `Negative: ${
                    data.negative?.count ?? 0
                } berita`;
        }

        async function loadNews() {
            hideError();
            setLoading(true);

            newsList.innerHTML = '';

            const params =
                new URLSearchParams();

            params.append(
                'limit',
                '50'
            );

            if (categoryFilter.value !== '') {
                params.append(
                    'category',
                    categoryFilter.value
                );
            }

            if (sentimentFilter.value !== '') {
                params.append(
                    'sentiment',
                    sentimentFilter.value
                );
            }

            if (
                searchInput.value.trim()
                !== ''
            ) {
                params.append(
                    'search',
                    searchInput.value.trim()
                );
            }

            try {
                const response = await fetch(
                    `${newsApiUrl}?${params.toString()}`,
                    {
                        headers: {
                            Accept:
                                'application/json',
                        },
                    }
                );

                const result =
                    await response.json();

                if (
                    ! response.ok
                    || ! result.success
                ) {
                    throw new Error(
                        result.message
                        ?? 'Data berita gagal dimuat.'
                    );
                }

                const summary =
                    result.meta
                        .sentiment_summary
                    ?? emptySentimentSummary();

                resultBadge.textContent =
                    `Showing: ${result.meta.count}`
                    + ` of ${result.meta.filtered_count}`;

                updateSentimentSummary(
                    summary
                );

                renderSentimentChart(
                    summary
                );

                if (
                    result.data.length === 0
                ) {
                    newsList.innerHTML = `
                        <div
                            class="
                                alert
                                alert-warning
                                mb-0
                            "
                        >
                            Berita tidak ditemukan.
                            Jalankan
                            php artisan news:sync.
                        </div>
                    `;

                    return;
                }

                newsList.innerHTML =
                    result.data
                        .map((article) => {
                            const sentiment =
                                article.sentiment;

                            const label =
                                sentiment
                                    ?.sentiment_label
                                ?? 'neutral';

                            const positiveWords =
                                normalizeWordArray(
                                    sentiment
                                        ?.matched_positive_words
                                );

                            const negativeWords =
                                normalizeWordArray(
                                    sentiment
                                        ?.matched_negative_words
                                );

                            return `
                                <article
                                    class="
                                        border
                                        rounded-3
                                        p-3
                                    "
                                >
                                    <div
                                        class="
                                            d-flex
                                            justify-content-between
                                            align-items-start
                                            gap-3
                                            mb-2
                                        "
                                    >
                                        <div>
                                            <div
                                                class="
                                                    d-flex
                                                    flex-wrap
                                                    gap-2
                                                    mb-2
                                                "
                                            >
                                                ${categoryBadge(
                                                    article.category
                                                )}

                                                ${sentimentBadge(
                                                    label
                                                )}
                                            </div>

                                            <h5 class="mb-1">
                                                ${escapeHtml(
                                                    article.title
                                                )}
                                            </h5>

                                            <div
                                                class="
                                                    text-secondary
                                                    small
                                                "
                                            >
                                                ${escapeHtml(
                                                    article
                                                        .source_name
                                                    ?? '-'
                                                )}
                                                ·
                                                ${formatDate(
                                                    article
                                                        .published_at
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mb-3">
                                        ${escapeHtml(
                                            article.description
                                            ?? 'Deskripsi tidak tersedia.'
                                        )}
                                    </p>

                                    <div
                                        class="
                                            small
                                            text-secondary
                                            mb-3
                                        "
                                    >
                                        Positive score:
                                        <strong>
                                            ${
                                                sentiment
                                                    ?.positive_score
                                                ?? 0
                                            }
                                        </strong>

                                        · Negative score:
                                        <strong>
                                            ${
                                                sentiment
                                                    ?.negative_score
                                                ?? 0
                                            }
                                        </strong>

                                        · Neutral score:
                                        <strong>
                                            ${
                                                sentiment
                                                    ?.neutral_score
                                                ?? 0
                                            }
                                        </strong>
                                    </div>

                                    ${renderMatchedWords(
                                        positiveWords,
                                        negativeWords
                                    )}

                                    <a
                                        href="${safeUrl(
                                            article.url
                                        )}"
                                        target="_blank"
                                        rel="
                                            noopener
                                            noreferrer
                                        "
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-primary
                                        "
                                    >
                                        Open Article
                                    </a>
                                </article>
                            `;
                        })
                        .join('');
            } catch (error) {
                showError(
                    error.message
                    ?? 'Terjadi kesalahan saat memuat berita.'
                );

                const emptySummary =
                    emptySentimentSummary();

                updateSentimentSummary(
                    emptySummary
                );

                renderSentimentChart(
                    emptySummary
                );
            } finally {
                setLoading(false);
            }
        }

        function renderSentimentChart(
            summary
        ) {
            const chartElement =
                document.getElementById(
                    'sentimentChart'
                );

            const counts = [
                summary.positive?.count ?? 0,
                summary.neutral?.count ?? 0,
                summary.negative?.count ?? 0,
            ];

            if (sentimentChart) {
                sentimentChart.destroy();
            }

            sentimentChart =
                new window.Chart(
                    chartElement,
                    {
                        type: 'doughnut',

                        data: {
                            labels: [
                                'Positive',
                                'Neutral',
                                'Negative',
                            ],

                            datasets: [
                                {
                                    data: counts,
                                },
                            ],
                        },

                        options: {
                            responsive: true,

                            maintainAspectRatio:
                                false,

                            plugins: {
                                legend: {
                                    position:
                                        'bottom',
                                },

                                tooltip: {
                                    callbacks: {
                                        label(
                                            context
                                        ) {
                                            const total =
                                                context
                                                    .dataset
                                                    .data
                                                    .reduce(
                                                        (
                                                            sum,
                                                            value
                                                        ) =>
                                                            sum
                                                            + Number(
                                                                value
                                                            ),
                                                        0
                                                    );

                                            const count =
                                                Number(
                                                    context.raw
                                                );

                                            const percentage =
                                                total > 0
                                                    ? (
                                                        count
                                                        / total
                                                        * 100
                                                    )
                                                    : 0;

                                            return (
                                                `${context.label}: `
                                                + `${count} berita `
                                                + `(${formatPercentage(
                                                    percentage
                                                )})`
                                            );
                                        },
                                    },
                                },
                            },
                        },
                    }
                );
        }

        categoryFilter.addEventListener(
            'change',
            loadNews
        );

        sentimentFilter.addEventListener(
            'change',
            loadNews
        );

        reloadButton.addEventListener(
            'click',
            loadNews
        );

        searchInput.addEventListener(
            'input',
            () => {
                window.clearTimeout(
                    searchTimer
                );

                searchTimer =
                    window.setTimeout(
                        loadNews,
                        400
                    );
            }
        );

        loadNews();
    </script>
@endpush