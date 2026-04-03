<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->string('id', 10)->primary(); // es. VQ
            $table->string('cruise_line_id', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->json('features')->nullable();
            $table->json('decks')->nullable();
            $table->timestamp('images_refreshed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_line_id')->references('id')->on('cruise_lines');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
};
