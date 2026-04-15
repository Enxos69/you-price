<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropIndex(['related_model_type', 'related_model_id']);
            $table->string('related_model_id', 64)->nullable()->change();
            $table->index(['related_model_type', 'related_model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropIndex(['related_model_type', 'related_model_id']);
            $table->unsignedBigInteger('related_model_id')->nullable()->change();
            $table->index(['related_model_type', 'related_model_id']);
        });
    }
};
