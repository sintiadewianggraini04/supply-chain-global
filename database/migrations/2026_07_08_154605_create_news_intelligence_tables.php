<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();

            $table->string('source_name')->nullable();
            $table->text('url')->unique();
            $table->text('image_url')->nullable();

            $table->string('category')->default('supply_chain');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->nullable();

            $table->json('raw_response')->nullable();

            $table->timestamps();

            $table->index('category');
            $table->index('published_at');
        });

        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });

        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });

        Schema::create('sentiment_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('news_article_id')
                ->constrained('news_cache')
                ->cascadeOnDelete();

            $table->unsignedInteger('positive_score')->default(0);
            $table->unsignedInteger('negative_score')->default(0);
            $table->unsignedInteger('neutral_score')->default(0);

            $table->string('sentiment_label')->default('neutral');

            $table->json('matched_positive_words')->nullable();
            $table->json('matched_negative_words')->nullable();

            $table->timestamps();

            $table->unique('news_article_id');
            $table->index('sentiment_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentiment_results');
        Schema::dropIfExists('negative_words');
        Schema::dropIfExists('positive_words');
        Schema::dropIfExists('news_cache');
    }
};