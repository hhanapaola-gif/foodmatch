<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanDaysTable extends Migration
{
    public function up(): void
    {
        Schema::create('plan_days', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('plan_id');
            $table->tinyInteger('day_of_week'); // 1 = Monday ... 7 = Sunday

            $table->unique(['plan_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_days');
    }
}
