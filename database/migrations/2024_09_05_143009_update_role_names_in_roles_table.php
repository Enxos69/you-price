<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('roles')
            ->where('name', 'admin')
            ->update(['name' => 'Amministratore']);

        DB::table('roles')
            ->where('name', 'user')
            ->update(['name' => 'Utente']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')
            ->where('name', 'Amministratore')
            ->update(['name' => 'admin']);

        DB::table('roles')
            ->where('name', 'Utente')
            ->update(['name' => 'user']);
    }
};
