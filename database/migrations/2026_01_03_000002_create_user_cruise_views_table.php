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
        Schema::create('user_cruise_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cruise_id');
            $table->unsignedInteger('view_count')->default(1)->comment('Numero di volte che l\'utente ha visualizzato questa crociera');
            $table->timestamp('last_viewed_at')->useCurrent()->comment('Ultima visualizzazione');
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
            $table->index(['user_id', 'last_viewed_at']);
            $table->index(['cruise_id', 'view_count']);
            
            // Una riga per ogni combinazione utente-crociera
            $table->unique(['user_id', 'cruise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cruise_views');
    }
};
