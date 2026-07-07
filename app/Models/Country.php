<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'official_name',
        'cca2',
        'cca3',
        'capital',
        'region',
        'subregion',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'languages',
        'population',
        'latitude',
        'longitude',
        'flag_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'population' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }
}