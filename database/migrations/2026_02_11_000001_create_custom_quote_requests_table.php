<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomQuoteRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('custom_quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('date_range');
            $table->decimal('budget', 10, 2);
            $table->integer('participants');
            $table->string('port_start')->nullable();
            $table->text('notes')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_quote_requests');
    }
}
