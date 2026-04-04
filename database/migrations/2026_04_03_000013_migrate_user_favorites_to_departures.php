<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_favorites', function (Blueprint $table) {
            $table->dropForeign(['cruise_id']);
            $table->dropUnique(['user_id', 'cruise_id']);
            $table->dropColumn('cruise_id');

            $table->string('departure_id', 30)->nullable()->after('user_id');
            $table->foreign('departure_id')->references('id')->on('departures')->cascadeOnDelete();
            $table->unique(['user_id', 'departure_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_favorites', function (Blueprint $table) {
            $table->dropForeign(['departure_id']);
            $table->dropUnique(['user_id', 'departure_id']);
            $table->dropColumn('departure_id');

            $table->unsignedBigInteger('cruise_id')->nullable()->after('user_id');
            $table->foreign('cruise_id')->references('id')->on('cruises')->cascadeOnDelete();
            $table->unique(['user_id', 'cruise_id']);
        });
    }
};
