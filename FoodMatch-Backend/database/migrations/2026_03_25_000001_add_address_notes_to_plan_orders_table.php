<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_orders', function (Blueprint $table) {
            $table->string('address')->nullable()->after('selected_days');
            $table->text('notes')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('plan_orders', function (Blueprint $table) {
            $table->dropColumn(['address', 'notes']);
        });
    }
};
