<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRichiestaStatoTable extends Migration
{
    /**
     * Esegui la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('richiesta_stato', function (Blueprint $table) {
            $table->id(); // campo id auto-incrementante
            $table->string('stato_richiesta', 255); // campo stato_richiesta di tipo stringa con lunghezza 255
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
        Schema::dropIfExists('richiesta_stato');
    }
}
