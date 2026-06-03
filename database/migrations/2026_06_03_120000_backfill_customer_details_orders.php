<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill customer details dari customer table ke orders table
        // untuk order yang dibuat sebelum migration 2026_05_25_105048
        DB::statement('
            UPDATE orders o
            JOIN customer c ON o.customer_id = c.customer_id
            LEFT JOIN users u ON c.user_id = u.id
            SET 
                o.customer_name = COALESCE(o.customer_name, c.nama),
                o.customer_email = COALESCE(o.customer_email, u.email),
                o.customer_phone = COALESCE(o.customer_phone, c.no_telp),
                o.customer_address = COALESCE(o.customer_address, c.alamat)
            WHERE o.customer_name IS NULL 
               OR o.customer_phone IS NULL 
               OR o.customer_address IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backfill tidak perlu di-reverse
    }
};
