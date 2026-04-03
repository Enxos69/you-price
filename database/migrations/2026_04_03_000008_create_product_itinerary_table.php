<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_itinerary', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 30);
            $table->string('port_id', 10);
            $table->tinyInteger('day_number')->unsigned();
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('port_id')->references('id')->on('ports');
            $table->unique(['product_id', 'day_number', 'port_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_itinerary');
    }
};
