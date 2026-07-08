<?php

namespace App\Services;

use App\Models\NegativeWord;
use App\Models\NewsArticle;
use App\Models\PositiveWord;
use App\Models\SentimentResult;

class NewsSentimentService
{
    public function analyze(NewsArticle $article): SentimentResult
    {
        $positiveWords = PositiveWord::query()
            ->pluck('word')
            ->map(fn ($word) => strtolower($word))
            ->toArray();

        $negativeWords = NegativeWord::query()
            ->pluck('word')
            ->map(fn ($word) => strtolower($word))
            ->toArray();

        $text = strtolower(
            $article->title . ' '
            . $article->description . ' '
            . $article->content
        );

        $words = preg_split('/[^a-z]+/', $text);

        $matchedPositiveWords = [];
        $matchedNegativeWords = [];

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            if (in_array($word, $positiveWords, true)) {
                $matchedPositiveWords[] = $word;
            }

            if (in_array($word, $negativeWords, true)) {
                $matchedNegativeWords[] = $word;
            }
        }

        $positiveScore = count($matchedPositiveWords);
        $negativeScore = count($matchedNegativeWords);

        $label = 'neutral';

        if ($positiveScore > $negativeScore) {
            $label = 'positive';
        }

        if ($negativeScore > $positiveScore) {
            $label = 'negative';
        }

        return SentimentResult::updateOrCreate(
            [
                'news_article_id' => $article->id,
            ],
            [
                'positive_score' => $positiveScore,
                'negative_score' => $negativeScore,
                'neutral_score' => $positiveScore === $negativeScore ? 1 : 0,
                'sentiment_label' => $label,
                'matched_positive_words' => array_values(
                    array_unique($matchedPositiveWords)
                ),
                'matched_negative_words' => array_values(
                    array_unique($matchedNegativeWords)
                ),
            ]
        );
    }
}