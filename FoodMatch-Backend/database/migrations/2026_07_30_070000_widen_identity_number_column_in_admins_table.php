<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `identity_number` was `VARCHAR(30)`, sized for a plain-text ID number.
     * Once the Admin model casts this attribute as `encrypted`, the stored
     * value becomes a base64-encoded JSON payload (iv/value/mac/tag) that is
     * 200-400+ characters even for a short plaintext, so the column must be
     * widened first or every save will fail with "Data too long for column
     * 'identity_number'". Same fix already applied to delivery_men — see
     * 2026_07_30_061927_widen_identity_number_column_in_delivery_men_table.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `admins` MODIFY `identity_number` TEXT NULL');
    }

    /**
     * Rolling back is only safe if the data has been decrypted back to
     * plain text first — encrypted values longer than 30 chars would be
     * truncated by this down-migration.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `admins` MODIFY `identity_number` VARCHAR(30) NULL");
    }
};
