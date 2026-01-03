<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cruise_id');
            $table->text('note')->nullable()->comment('Note personali utente sulla crociera');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('cruise_id')
                  ->references('id')
                  ->on('cruises')
                  ->onDelete('cascade');
            
            // Indici per performance
            $table->index(['user_id', 'created_at']);
            
            // Un utente può salvare una crociera una sola volta
            $table->unique(['user_id', 'cruise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
