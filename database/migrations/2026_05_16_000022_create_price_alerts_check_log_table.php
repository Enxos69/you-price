<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alerts_check_log', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->integer('alerts_checked')->default(0);
            $table->integer('alerts_triggered')->default(0);
            $table->integer('alerts_skipped')->default(0);
            $table->integer('emails_failed')->default(0);
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts_check_log');
    }
};
