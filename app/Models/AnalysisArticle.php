<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisArticle extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}