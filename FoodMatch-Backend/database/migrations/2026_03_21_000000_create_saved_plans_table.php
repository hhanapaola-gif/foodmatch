<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedPlansTable extends Migration
{
    public function up(): void
    {
        Schema::create('saved_plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('plan_id');
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_plans');
    }
}
