<?php

namespace App\Services;

use App\Models\NegativeWord;
use App\Models\NewsArticle;
use App\Models\PositiveWord;
use App\Models\SentimentResult;

class NewsSentimentService
{
    private ?array $positiveLookup = null;

    private ?array $negativeLookup = null;

    public function analyze(
        NewsArticle $article
    ): SentimentResult {
        $positiveWords =
            $this->getPositiveWords();

        $negativeWords =
            $this->getNegativeWords();

        /*
         * Gabungkan seluruh bagian berita yang akan dianalisis.
         */
        $text = implode(
            ' ',
            [
                $article->title ?? '',
                $article->description ?? '',
                $article->content ?? '',
            ]
        );

        /*
         * Menghapus tag HTML, mengubah entity HTML,
         * dan mengubah semua huruf menjadi kecil.
         */
        $text = strtolower(
            html_entity_decode(
                strip_tags($text),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );

        /*
         * Berita dari GNews umumnya berbahasa Inggris.
         * Setiap karakter selain a-z digunakan sebagai pemisah.
         */
        $words = preg_split(
            '/[^a-z]+/',
            $text
        ) ?: [];

        $matchedPositiveWords = [];
        $matchedNegativeWords = [];

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            $word = trim($word);

            if ($word === '') {
                continue;
            }

            if (
                $this->matchesLexicon(
                    $word,
                    $positiveWords
                )
            ) {
                $positiveScore++;

                $matchedPositiveWords[] =
                    $word;
            }

            if (
                $this->matchesLexicon(
                    $word,
                    $negativeWords
                )
            ) {
                $negativeScore++;

                $matchedNegativeWords[] =
                    $word;
            }
        }

        $label = 'neutral';

        if ($positiveScore > $negativeScore) {
            $label = 'positive';
        } elseif (
            $negativeScore > $positiveScore
        ) {
            $label = 'negative';
        }

        return SentimentResult::updateOrCreate(
            [
                'news_article_id' =>
                    $article->id,
            ],
            [
                'positive_score' =>
                    $positiveScore,

                'negative_score' =>
                    $negativeScore,

                /*
                 * Neutral score digunakan sebagai indikator:
                 * 1 = sentimen neutral
                 * 0 = bukan neutral
                 */
                'neutral_score' =>
                    $label === 'neutral'
                        ? 1
                        : 0,

                'sentiment_label' =>
                    $label,

                /*
                 * Kata disimpan secara unik untuk ditampilkan
                 * pada halaman News Intelligence.
                 */
                'matched_positive_words' =>
                    array_values(
                        array_unique(
                            $matchedPositiveWords
                        )
                    ),

                'matched_negative_words' =>
                    array_values(
                        array_unique(
                            $matchedNegativeWords
                        )
                    ),
            ]
        );
    }

    /**
     * Mencocokkan kata asli dan beberapa variasi katanya.
     *
     * Contoh:
     * increases  -> increase
     * decreased  -> decrease
     * improving  -> improve
     * delayed    -> delay
     */
    private function matchesLexicon(
        string $word,
        array $lookup
    ): bool {
        foreach (
            $this->createWordCandidates($word)
            as $candidate
        ) {
            if (isset($lookup[$candidate])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Membuat beberapa kandidat bentuk dasar kata.
     *
     * Ini bukan machine-learning stemming.
     * Proses ini hanya normalisasi sederhana agar tetap
     * sesuai dengan metode lexicon based.
     */
    private function createWordCandidates(
        string $word
    ): array {
        $candidates = [
            $word,
        ];

        $length = strlen($word);

        /*
         * companies -> company
         */
        if (
            $length > 4
            && str_ends_with($word, 'ies')
        ) {
            $candidates[] =
                substr($word, 0, -3) . 'y';
        }

        /*
         * increases -> increase
         * improves  -> improve
         */
        if (
            $length > 3
            && str_ends_with($word, 's')
        ) {
            $candidates[] =
                substr($word, 0, -1);
        }

        /*
         * crises atau kata lain berakhiran es.
         */
        if (
            $length > 4
            && str_ends_with($word, 'es')
        ) {
            $candidates[] =
                substr($word, 0, -2);
        }

        /*
         * delayed -> delay
         * improved -> improve
         */
        if (
            $length > 4
            && str_ends_with($word, 'ed')
        ) {
            $candidates[] =
                substr($word, 0, -2);

            /*
             * improved -> improve
             * increased -> increase
             */
            $candidates[] =
                substr($word, 0, -1);
        }

        /*
         * improving -> improve
         * increasing -> increase
         */
        if (
            $length > 5
            && str_ends_with($word, 'ing')
        ) {
            $base =
                substr($word, 0, -3);

            $candidates[] = $base;
            $candidates[] = $base . 'e';
        }

        return array_values(
            array_unique(
                array_filter(
                    $candidates
                )
            )
        );
    }

    private function getPositiveWords(): array
    {
        if ($this->positiveLookup === null) {
            $words = PositiveWord::query()
                ->pluck('word')
                ->map(
                    fn ($word) =>
                        strtolower(
                            trim($word)
                        )
                )
                ->filter()
                ->values()
                ->all();

            $this->positiveLookup =
                array_fill_keys(
                    $words,
                    true
                );
        }

        return $this->positiveLookup;
    }

    private function getNegativeWords(): array
    {
        if ($this->negativeLookup === null) {
            $words = NegativeWord::query()
                ->pluck('word')
                ->map(
                    fn ($word) =>
                        strtolower(
                            trim($word)
                        )
                )
                ->filter()
                ->values()
                ->all();

            $this->negativeLookup =
                array_fill_keys(
                    $words,
                    true
                );
        }

        return $this->negativeLookup;
    }
}