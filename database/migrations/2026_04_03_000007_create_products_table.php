<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('id', 30)->primary(); // productID es. VQMIAMIA247323
            $table->string('cruise_line_id', 10);
            $table->string('ship_id', 10);
            $table->string('area_id', 20);
            $table->string('port_from_id', 10);
            $table->string('port_to_id', 10);
            $table->string('cruise_name');
            $table->boolean('is_package')->default(false);
            $table->string('matchcode')->nullable();
            $table->boolean('sea')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_line_id')->references('id')->on('cruise_lines');
            $table->foreign('ship_id')->references('id')->on('ships');
            $table->foreign('area_id')->references('id')->on('areas');
            $table->foreign('port_from_id')->references('id')->on('ports');
            $table->foreign('port_to_id')->references('id')->on('ports');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
