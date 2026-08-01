<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('restaurant_id')->nullable();
            $table->bigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');              // weekly | monthly
            $table->integer('min_days')->default(1);
            $table->integer('meals_per_day')->default(1);
            $table->decimal('breakfast_price', 8, 2)->default(0.00);
            $table->decimal('lunch_price', 8, 2)->default(0.00);
            $table->decimal('dinner_price', 8, 2)->default(0.00);
            $table->string('image')->nullable();
            $table->string('menu_pdf')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
}
