<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->date('start_date');
            $table->string('status', 20)->default('pending');     // pending | confirmed | cancelled
            $table->string('payment_status', 20)->default('unpaid'); // unpaid | paid
            $table->string('payment_method', 30)->default('demo');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->json('selected_days')->nullable();             // ['mon','tue','wed',...]
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_orders');
    }
};
