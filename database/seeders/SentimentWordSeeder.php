<?php

namespace Database\Seeders;

use App\Models\NegativeWord;
use App\Models\PositiveWord;
use Illuminate\Database\Seeder;

class SentimentWordSeeder extends Seeder
{
    public function run(): void
    {
        $positiveWords = [
            'growth',
            'increase',
            'profit',
            'stable',
            'improve',
            'recovery',
            'strong',
            'surplus',
            'efficient',
            'resilient',
            'expand',
            'boost',
            'secure',
            'optimistic',
            'opportunity',
        ];

        $negativeWords = [
            'war',
            'crisis',
            'inflation',
            'delay',
            'disaster',
            'conflict',
            'shortage',
            'strike',
            'congestion',
            'disruption',
            'decline',
            'risk',
            'weak',
            'volatile',
            'sanction',
        ];

        foreach ($positiveWords as $word) {
            PositiveWord::updateOrCreate([
                'word' => $word,
            ]);
        }

        foreach ($negativeWords as $word) {
            NegativeWord::updateOrCreate([
                'word' => $word,
            ]);
        }
    }
}