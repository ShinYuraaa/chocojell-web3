<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\BlockchainHelper;

class RecordPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain:record-pending
                            {--max-retries=3 : Maximum retry attempts per order}
                            {--limit=10 : Maximum orders to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record pending orders to blockchain (retry failed recordings)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $maxRetries = (int) $this->option('max-retries');
        $limit = (int) $this->option('limit');

        $this->info("Processing pending orders (max retries: {$maxRetries}, limit: {$limit})...");

        // Ambil orders dengan status pending dan retry count < max
        $pendingOrders = DB::table('orders')
            ->where('blockchain_status', 'pending')
            ->where('blockchain_retry_count', '<', $maxRetries)
            ->limit($limit)
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending orders to record.');
            return 0;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($pendingOrders as $order) {
            try {
                $this->recordOrderToBlockchain($order);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Order ID {$order->order_id}: {$e->getMessage()}");
                $failureCount++;
            }
        }

        $this->info("Completed! Success: {$successCount}, Failed: {$failureCount}");

        return 0;
    }

    /**
     * Record single order to blockchain
     */
    private function recordOrderToBlockchain(object $order): void
    {
        // Generate hash
        $orderHash = BlockchainHelper::generateOrderHashWeb3(
            $order->order_id,
            $order->customer_id,
            $order->total_price,
            \Carbon\Carbon::parse($order->order_date)->format('Y-m-d')
        );

        // Record to blockchain
        $result = BlockchainHelper::recordOrderToBlockchain(
            $order->order_id,
            $order->customer_id,
            $order->total_price,
            $orderHash
        );

        if ($result['success']) {
            // Update order dengan blockchain info
            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'blockchain_hash' => $orderHash,
                    'blockchain_tx_hash' => $result['tx_hash'],
                    'blockchain_recorded_at' => now(),
                    'blockchain_status' => 'recorded',
                    'blockchain_retry_count' => $order->blockchain_retry_count,
                    'updated_at' => now(),
                ]);

            $this->line("<info>✓</info> Order ID {$order->order_id} recorded successfully (TX: {$result['tx_hash']})");
        } else {
            // Increment retry count
            DB::table('orders')
                ->where('order_id', $order->order_id)
                ->update([
                    'blockchain_retry_count' => $order->blockchain_retry_count + 1,
                    'blockchain_status' => $order->blockchain_retry_count + 1 >= 3 ? 'failed' : 'pending',
                    'updated_at' => now(),
                ]);

            throw new \Exception($result['error'] ?? 'Unknown blockchain error');
        }
    }
}
