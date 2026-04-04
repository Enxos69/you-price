<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_activities')->truncate();
        DB::table('user_cruise_views')->truncate();
        DB::table('user_favorites')->truncate();
        DB::table('search_logs')->truncate();
    }

    public function down(): void
    {
        // I dati eliminati non sono ripristinabili
    }
};
