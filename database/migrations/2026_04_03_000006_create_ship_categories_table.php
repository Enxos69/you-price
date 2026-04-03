<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ship_categories', function (Blueprint $table) {
            $table->id();
            $table->string('ship_id', 10);
            $table->string('cl_cat', 10);          // categoria della cruise line, es. 4A
            $table->string('cruisehost_cat', 10);  // categoria CruiseHost, es. IS
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('ship_id')->references('id')->on('ships')->cascadeOnDelete();
            $table->unique(['ship_id', 'cl_cat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_categories');
    }
};
