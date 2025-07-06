<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbilitatoToUsersTable extends Migration
{
    /**
     * Esegui le modifiche alla tabella.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Aggiungi la colonna 'abilitato' con un valore predefinito di 0
            $table->boolean('abilitato')->default(0)->after('email');
        });
    }

    /**
     * Ripristina le modifiche alla tabella.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Rimuovi la colonna 'abilitato' se esiste
            $table->dropColumn('abilitato');
        });
    }
}
