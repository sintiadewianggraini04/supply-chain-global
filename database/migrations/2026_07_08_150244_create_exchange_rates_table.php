<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            $table->string('base_currency', 10);
            $table->string('target_currency', 10);

            $table->decimal('rate', 20, 8);

            $table->date('rate_date');
            $table->timestamp('fetched_at')->nullable();

            $table->string('provider')->default('ExchangeRate-API');
            $table->json('raw_response')->nullable();

            $table->timestamps();

            $table->unique([
                'base_currency',
                'target_currency',
                'rate_date',
            ], 'exchange_rates_unique_daily_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};