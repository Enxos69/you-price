<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRichiestaTable extends Migration
{
    /**
     * Esegui la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('richiesta', function (Blueprint $table) {
            $table->id(); // campo id auto-incrementante
            $table->foreignId('id_utente')->constrained('users')->onDelete('cascade'); // chiave esterna verso la tabella users
            $table->foreignId('id_richiesta_tipo')->constrained('richiesta_tipo')->onDelete('cascade'); // chiave esterna verso la tabella richiesta_tipo
            $table->foreignId('id_richiesta_stato')->constrained('richiesta_stato')->onDelete('cascade'); // chiave esterna verso la tabella richiesta_stato
            $table->date('data_fine_validita'); // campo per la data di fine validità
            $table->decimal('budget', 10, 2); // campo per il budget, con 10 cifre totali e 2 decimali
            $table->integer('rating')->nullable(); // campo per il rating, opzionale
            $table->text('note')->nullable(); // campo per le note, opzionale
            $table->timestamps(); // opzionale, per aggiungere i campi created_at e updated_at
        });
    }

    /**
     * Reverti la migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('richiesta');
    }
}
