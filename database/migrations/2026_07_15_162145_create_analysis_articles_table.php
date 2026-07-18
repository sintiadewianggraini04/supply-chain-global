<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analysis_articles',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category', 100);

                $table->text('summary')->nullable();
                $table->longText('content');

                $table->string('status', 20)
                    ->default('draft');

                $table->timestamp(
                    'published_at'
                )->nullable();

                $table->timestamps();

                $table->index('status');
                $table->index('category');
                $table->index('published_at');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'analysis_articles'
        );
    }
};