<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentimentResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_article_id',
        'positive_score',
        'negative_score',
        'neutral_score',
        'sentiment_label',
        'matched_positive_words',
        'matched_negative_words',
    ];

    protected function casts(): array
    {
        return [
            'matched_positive_words' => 'array',
            'matched_negative_words' => 'array',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'news_article_id');
    }
}