<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('status');
            $table->string('tx_hash', 66)->nullable()->after('payment_method');
            $table->string('wallet_address', 42)->nullable()->after('tx_hash');
            $table->unsignedBigInteger('chain_id')->nullable()->after('wallet_address');
            $table->decimal('paid_amount_eth', 18, 8)->nullable()->after('chain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'tx_hash',
                'wallet_address',
                'chain_id',
                'paid_amount_eth',
            ]);
        });
    }
};
