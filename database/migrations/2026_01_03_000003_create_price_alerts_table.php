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
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cruise_id');
            
            // Configurazione alert
            $table->decimal('target_price', 10, 2)->comment('Prezzo target desiderato dall\'utente');
            $table->enum('cabin_type', ['interior', 'oceanview', 'balcony', 'minisuite', 'suite'])
                  ->default('balcony')
                  ->comment('Tipo di cabina da monitorare');
            $table->enum('alert_type', ['fixed_price', 'percentage_discount'])
                  ->default('fixed_price')
                  ->comment('Tipo di alert: prezzo fisso o sconto percentuale');
            $table->decimal('percentage_threshold', 5, 2)->nullable()
                  ->comment('Soglia percentuale di sconto (es. 15.00 = 15%)');
            
            // Stato e tracking
            $table->boolean('is_active')->default(true)->comment('Se l\'alert è attivo');
            $table->decimal('current_price', 10, 2)->nullable()->comment('Ultimo prezzo rilevato');
            $table->timestamp('last_checked_at')->nullable()->comment('Ultimo controllo del prezzo');
            $table->timestamp('last_notification_sent_at')->nullable()->comment('Ultima notifica inviata');
            $table->boolean('notification_sent')->default(false)->comment('Se è stata inviata una notifica');
            
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
            $table->index(['user_id', 'is_active']);
            $table->index(['cruise_id', 'is_active']);
            $table->index('last_checked_at');
            $table->index(['is_active', 'notification_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
