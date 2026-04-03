<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ship_cabin_images', function (Blueprint $table) {
            $table->id();
            $table->string('ship_id', 10);
            $table->string('category_code', 10); // es. IS, BK
            $table->string('image_url');
            $table->string('gallery_name')->nullable();
            $table->timestamps();

            $table->foreign('ship_id')->references('id')->on('ships')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_cabin_images');
    }
};
