<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScoreSnapshot extends Model
{
    protected $fillable = [
        'country_id',
        'weather_score',
        'inflation_score',
        'news_score',
        'currency_score',
        'final_score',
        'level',
        'recorded_on',
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'weather_score' => 'integer',
            'inflation_score' => 'integer',
            'news_score' => 'integer',
            'currency_score' => 'integer',
            'final_score' => 'integer',
            'recorded_on' => 'date',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(
            Country::class
        );
    }
}