<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_history', function (Blueprint $table) {
            // Indice covering per MAX(id) GROUP BY departure_id, category_code
            // usato da syncProducts per trovare l'ultimo prezzo registrato.
            // Senza questo, la subquery fa una scansione su 240k righe × 3233 prodotti.
            $table->index(['departure_id', 'category_code', 'id'], 'ph_dep_cat_id');
        });
    }

    public function down(): void
    {
        Schema::table('price_history', function (Blueprint $table) {
            $table->dropIndex('ph_dep_cat_id');
        });
    }
};
