<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departures', function (Blueprint $table) {
            $table->string('id', 30)->primary(); // master_cruise_id es. VQ247323260406
            $table->string('product_id', 30);
            $table->date('dep_date');
            $table->date('arr_date');
            $table->tinyInteger('duration')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products');
            $table->index('dep_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departures');
    }
};
