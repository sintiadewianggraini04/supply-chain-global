<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Aman jika migration dengan tabel yang sama
         * ternyata sudah pernah dijalankan.
         */
        if (
            Schema::hasTable(
                'risk_score_snapshots'
            )
        ) {
            return;
        }

        Schema::create(
            'risk_score_snapshots',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('country_id')
                    ->constrained('countries')
                    ->cascadeOnDelete();

                $table
                    ->unsignedTinyInteger(
                        'weather_score'
                    )
                    ->nullable();

                $table
                    ->unsignedTinyInteger(
                        'inflation_score'
                    )
                    ->nullable();

                $table
                    ->unsignedTinyInteger(
                        'news_score'
                    )
                    ->nullable();

                $table
                    ->unsignedTinyInteger(
                        'currency_score'
                    )
                    ->nullable();

                $table
                    ->unsignedTinyInteger(
                        'final_score'
                    );

                $table
                    ->string(
                        'level',
                        20
                    );

                $table
                    ->date(
                        'recorded_on'
                    );

                $table->timestamps();

                $table->unique(
                    [
                        'country_id',
                        'recorded_on',
                    ],
                    'risk_snapshot_country_date_unique'
                );

                $table->index(
                    'recorded_on',
                    'risk_snapshot_recorded_on_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'risk_score_snapshots'
        );
    }
};