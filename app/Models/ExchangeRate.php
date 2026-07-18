<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'rate_date',
        'fetched_at',
        'provider',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:10',
            'rate_date' => 'date',
            'fetched_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }
}