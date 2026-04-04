<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_alerts', function (Blueprint $table) {
            $table->dropForeign(['cruise_id']);
            $table->dropIndex(['cruise_id', 'is_active']);
            $table->dropColumn('cruise_id');

            $table->string('departure_id', 30)->nullable()->after('user_id');
            $table->foreign('departure_id')->references('id')->on('departures')->cascadeOnDelete();
            $table->index(['departure_id', 'is_active']);

            // cabin_type (enum fisso) → category_code (stringa libera, es. IS, BK, 4A)
            $table->dropColumn('cabin_type');
            $table->string('category_code', 10)->default('')->after('target_price');
        });
    }

    public function down(): void
    {
        Schema::table('price_alerts', function (Blueprint $table) {
            $table->dropForeign(['departure_id']);
            $table->dropIndex(['departure_id', 'is_active']);
            $table->dropColumn('departure_id');

            $table->unsignedBigInteger('cruise_id')->nullable()->after('user_id');
            $table->foreign('cruise_id')->references('id')->on('cruises')->cascadeOnDelete();
            $table->index(['cruise_id', 'is_active']);

            $table->dropColumn('category_code');
            $table->enum('cabin_type', ['interior', 'oceanview', 'balcony', 'minisuite', 'suite'])
                ->default('balcony')->after('target_price');
        });
    }
};
