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
        // Restore payment_proof_path untuk order #33
        DB::table('orders')
            ->where('order_id', 33)
            ->update(['payment_proof_path' => 'private/payment_proofs/proof_33_1779674862.jpeg']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')
            ->where('order_id', 33)
            ->update(['payment_proof_path' => null]);
    }
};
