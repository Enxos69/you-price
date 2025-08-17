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
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            
            // Informazioni utente
            $table->unsignedBigInteger('user_id')->nullable(); // ID utente se registrato
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Parametri di ricerca
            $table->string('date_range')->nullable(); // Range di date ricercato
            $table->decimal('budget', 10, 2)->nullable(); // Budget totale
            $table->integer('participants')->nullable(); // Numero partecipanti
            $table->string('port_start')->nullable(); // Porto di partenza
            $table->string('port_end')->nullable(); // Porto di arrivo
            
            // Risultati della ricerca
            $table->integer('total_matches')->default(0); // Numero risultati compatibili
            $table->integer('total_alternatives')->default(0); // Numero alternative
            $table->decimal('satisfaction_score', 5, 2)->default(0); // Punteggio soddisfazione
            $table->decimal('optimization_score', 5, 2)->default(0); // Punteggio ottimizzazione
            $table->decimal('avg_price_found', 10, 2)->nullable(); // Prezzo medio trovato
            $table->decimal('avg_savings', 5, 2)->nullable(); // Risparmio medio %
            $table->integer('companies_found')->default(0); // Numero compagnie trovate
            $table->decimal('avg_duration', 5, 2)->nullable(); // Durata media in giorni
            
            // Informazioni tecniche e di sessione
            $table->string('ip_address', 45)->nullable(); // IP dell'utente (supporta IPv6)
            $table->string('user_agent')->nullable(); // User agent completo
            $table->string('device_type', 50)->nullable(); // mobile, desktop, tablet
            $table->string('operating_system', 100)->nullable(); // OS dell'utente
            $table->string('browser', 100)->nullable(); // Browser utilizzato
            $table->string('browser_version', 50)->nullable(); // Versione browser
            $table->string('platform', 100)->nullable(); // Piattaforma (Windows, macOS, etc)
            $table->boolean('is_mobile')->default(false); // Se è dispositivo mobile
            $table->boolean('is_tablet')->default(false); // Se è tablet
            $table->boolean('is_desktop')->default(true); // Se è desktop
            $table->string('screen_resolution', 20)->nullable(); // Risoluzione schermo se disponibile
            
            // Geolocalizzazione (se disponibile)
            $table->string('country', 100)->nullable(); // Paese
            $table->string('region', 100)->nullable(); // Regione/Stato
            $table->string('city', 100)->nullable(); // Città
            $table->string('timezone', 50)->nullable(); // Fuso orario
            $table->decimal('latitude', 10, 8)->nullable(); // Latitudine
            $table->decimal('longitude', 11, 8)->nullable(); // Longitudine
            
            // Provider e connessione
            $table->string('isp', 255)->nullable(); // Internet Service Provider
            $table->string('connection_type', 50)->nullable(); // Tipo connessione se rilevabile
            
            // Informazioni sulla sessione
            $table->string('session_id')->nullable(); // ID sessione
            $table->string('referrer')->nullable(); // Pagina di provenienza
            $table->string('language', 10)->nullable(); // Lingua del browser
            $table->json('search_suggestions')->nullable(); // Consigli generati dall'AI
            
            // Metriche di performance
            $table->integer('search_duration_ms')->nullable(); // Durata ricerca in millisecondi
            $table->boolean('search_successful')->default(true); // Se la ricerca è andata a buon fine
            $table->text('error_message')->nullable(); // Eventuale messaggio di errore
            
            // Informazioni aggiuntive
            $table->boolean('is_guest')->default(true); // Se è un utente ospite
            $table->timestamp('search_date')->useCurrent(); // Data e ora della ricerca
            $table->json('additional_data')->nullable(); // Campo JSON per dati aggiuntivi futuri
            
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index(['user_id', 'search_date']);
            $table->index(['ip_address', 'search_date']);
            $table->index(['device_type', 'search_date']);
            $table->index(['country', 'search_date']);
            $table->index(['search_successful', 'search_date']);
            $table->index('search_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};