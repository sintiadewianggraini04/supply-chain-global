<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NewsArticle extends Model
{
    use HasFactory;

    protected $table = 'news_cache';

    protected $fillable = [
        'title',
        'description',
        'content',
        'source_name',
        'url',
        'image_url',
        'category',
        'published_at',
        'fetched_at',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function sentiment(): HasOne
    {
        return $this->hasOne(SentimentResult::class, 'news_article_id');
    }
}