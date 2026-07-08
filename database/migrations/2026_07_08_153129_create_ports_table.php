<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('country_name')->nullable();
            $table->string('country_code', 3)->nullable();

            $table->string('port_code', 20)->nullable();
            $table->string('port_type')->default('seaport');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->unsignedTinyInteger('congestion_level')->default(0);
            $table->string('risk_level')->default('low');

            $table->text('notes')->nullable();
            $table->string('source')->default('World Port Dataset Sample');

            $table->timestamps();

            $table->index('country_code');
            $table->index('risk_level');

            $table->unique([
                'name',
                'country_code',
            ], 'ports_unique_name_country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};