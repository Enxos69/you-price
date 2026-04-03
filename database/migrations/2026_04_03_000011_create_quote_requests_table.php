<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('departure_id', 30);
            $table->string('vacancy_id')->nullable(); // ID vacancy restituito dall'API
            $table->enum('status', ['pending', 'processing', 'quoted', 'booked', 'cancelled'])->default('pending');
            $table->tinyInteger('adults')->unsigned()->default(2);
            $table->json('price_snapshot')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('departure_id')->references('id')->on('departures');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
