<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_order_id');
            $table->string('meal_type', 20);   // breakfast | lunch | dinner
            $table->tinyInteger('day_of_week'); // 1=Mon … 7=Sun
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            $table->index('plan_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_order_details');
    }
};
