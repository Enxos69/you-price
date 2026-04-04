<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departures', function (Blueprint $table) {
            $table->decimal('min_price', 10, 2)->nullable()->after('duration');
            $table->index('min_price');
        });

        // Popola min_price dai prezzi esistenti in price_history
        DB::statement('
            UPDATE departures d
            INNER JOIN (
                SELECT departure_id, MIN(price) AS min_p
                FROM price_history
                GROUP BY departure_id
            ) ph ON d.id = ph.departure_id
            SET d.min_price = ph.min_p
        ');
    }

    public function down(): void
    {
        Schema::table('departures', function (Blueprint $table) {
            $table->dropIndex(['min_price']);
            $table->dropColumn('min_price');
        });
    }
};
