<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_history', function (Blueprint $table) {
            $table->id();
            $table->string('departure_id', 30);
            $table->string('category_code', 10); // es. IS, BK, 4A
            $table->decimal('price', 10, 2);
            $table->char('currency', 3)->default('EUR');
            $table->timestamp('recorded_at')->useCurrent();
            $table->enum('source', ['catalog', 'api'])->default('catalog');

            $table->foreign('departure_id')->references('id')->on('departures');
            $table->index(['departure_id', 'category_code', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_history');
    }
};
