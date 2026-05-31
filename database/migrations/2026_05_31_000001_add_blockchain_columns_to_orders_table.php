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
            $table->string('blockchain_hash', 66)->nullable()->after('updated_at')->comment('Keccak256 hash dari order ID');
            $table->string('blockchain_tx_hash', 66)->nullable()->after('blockchain_hash')->comment('Transaction hash dari blockchain');
            $table->timestamp('blockchain_recorded_at')->nullable()->after('blockchain_tx_hash')->comment('Waktu recorded ke blockchain');
            $table->enum('blockchain_status', ['pending', 'recorded', 'failed'])->default('pending')->after('blockchain_recorded_at')->comment('Status recording ke blockchain');
            $table->unsignedTinyInteger('blockchain_retry_count')->default(0)->after('blockchain_status')->comment('Jumlah retry attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'blockchain_hash',
                'blockchain_tx_hash',
                'blockchain_recorded_at',
                'blockchain_status',
                'blockchain_retry_count',
            ]);
        });
    }
};
