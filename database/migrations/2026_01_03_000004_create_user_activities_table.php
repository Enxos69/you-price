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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Tipo di attività
            $table->enum('activity_type', [
                'search',          // Ricerca crociera
                'view',            // Visualizzazione dettagli
                'favorite_add',    // Aggiunta ai preferiti
                'favorite_remove', // Rimozione dai preferiti
                'alert_create',    // Creazione alert
                'alert_modify',    // Modifica alert
                'alert_delete',    // Cancellazione alert
                'share',           // Condivisione crociera
                'download',        // Download dettagli
                'contact'          // Contatto richiesta info
            ])->index();
            
            // Relazione polimorfica con il modello correlato
            $table->string('related_model_type')->nullable()
                  ->comment('Classe del modello correlato (es: App\\Models\\Cruise)');
            $table->unsignedBigInteger('related_model_id')->nullable()
                  ->comment('ID del modello correlato');
            
            // Metadati aggiuntivi
            $table->json('metadata')->nullable()
                  ->comment('Dati aggiuntivi specifici per tipo attività');
            
            // Informazioni tecniche
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Indici per performance
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            $table->index(['related_model_type', 'related_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
