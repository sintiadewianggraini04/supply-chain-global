<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('official_name')->nullable();

            $table->string('cca2', 2)->nullable()->unique();
            $table->string('cca3', 3)->nullable()->unique();

            $table->string('capital')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();

            $table->string('currency_code', 10)->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_symbol', 10)->nullable();

            $table->json('languages')->nullable();

            $table->unsignedBigInteger('population')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('flag_url')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};