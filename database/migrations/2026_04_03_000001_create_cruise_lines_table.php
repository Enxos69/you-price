<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cruise_lines', function (Blueprint $table) {
            $table->string('id', 10)->primary(); // es. CCL, CEL
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->boolean('is_online')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cruise_lines');
    }
};
