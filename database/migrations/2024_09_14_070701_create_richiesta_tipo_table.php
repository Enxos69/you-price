<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRichiestaTipoTable extends Migration
{
    /**
     * Esegui la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('richiesta_tipo', function (Blueprint $table) {
            $table->id(); // campo id auto-incrementante
            $table->string('tipo_richiesta', 255); // campo tipo_richiesta di tipo stringa con lunghezza 255
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
        Schema::dropIfExists('richiesta_tipo');
    }
}

