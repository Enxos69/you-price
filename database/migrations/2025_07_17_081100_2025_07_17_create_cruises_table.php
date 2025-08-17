<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('cruises')) {
        Schema::create('cruises', function (Blueprint $table) {
            $table->id();
            $table->string('ship');
            $table->string('cruise');
            $table->string('duration')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('details')->nullable();
            $table->string('line');
            $table->unsignedTinyInteger('night')->nullable();
            $table->date('partenza')->nullable();
            $table->date('arrivo')->nullable();
            $table->string('interior')->nullable();
            $table->string('oceanview')->nullable();
            $table->string('balcony')->nullable();
            $table->string('minisuite')->nullable();
            $table->string('suite')->nullable();
            $table->timestamps();
        });}
    }

    public function down(): void {
        Schema::dropIfExists('cruises');
    }
};