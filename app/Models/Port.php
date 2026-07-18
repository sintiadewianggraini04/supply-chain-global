<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = [
        'name',
        'country_name',
        'country_code',
        'port_code',
        'port_type',
        'latitude',
        'longitude',
        'congestion_level',
        'risk_level',
        'notes',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'congestion_level' => 'integer',
        ];
    }
}