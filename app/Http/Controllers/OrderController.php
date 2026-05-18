<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    private function rpcRequest(string $method, array $params = [])
    {
        $rpcUrl = env('WEB3_RPC_URL', 'http://127.0.0.1:7545');

        $payload = json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 12,
            ],
        ]);

        $response = @file_get_contents($rpcUrl, false, $context);
        if ($response === false) {
            throw new \RuntimeException('Gagal terhubung ke Ganache RPC.');
        }

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            $message = $json['error']['message'] ?? 'Unknown RPC error';
            throw new \RuntimeException('RPC error: ' . $message);
        }

        return $json['result'] ?? null;
    }

    private function ethToWeiHex(float $ethAmount): string
    {
        $wei = (int) round($ethAmount * 1000000000000000000);

        if ($wei <= 0) {
            throw new \RuntimeException('Nilai ETH tidak valid.');
        }

        return '0x' . dechex($wei);
    }

    private function processGanacheDirectPayment(float $payAmountEth): array
    {
        $accounts = $this->rpcRequest('eth_accounts');
        if (!is_array($accounts) || empty($accounts)) {
            throw new \RuntimeException('Akun Ganache tidak ditemukan.');
        }

        $fromAddress = env('GANACHE_FROM_ADDRESS');
        if (!$fromAddress) {
            $fromAddress = $accounts[0];
        }

        $receiverAddress = env('WEB3_RECEIVER_ADDRESS');
        if (!$receiverAddress) {
            // Default aman untuk ujicoba: kirim ke akun Ganache lain (bukan kontrak)
            $receiverAddress = $accounts[1] ?? $accounts[0];
        }

        if (strtolower($receiverAddress) === strtolower($fromAddress)) {
            $otherAccounts = array_values(array_filter($accounts, fn ($acc) => strtolower($acc) !== strtolower($fromAddress)));
            if (!empty($otherAccounts)) {
                $receiverAddress = $otherAccounts[0];
            }
        }

        $receiverCode = $this->rpcRequest('eth_getCode', [$receiverAddress, 'latest']);
        if (is_string($receiverCode) && $receiverCode !== '0x') {
            throw new \RuntimeException('WEB3_RECEIVER_ADDRESS mengarah ke smart contract. Untuk mode Ganache langsung, gunakan alamat wallet Ganache (EOA), bukan alamat contract.');
        }

        $valueHex = $this->ethToWeiHex($payAmountEth);

        // Preflight supaya alasan gagal lebih cepat terlihat sebelum sendTransaction
        $this->rpcRequest('eth_estimateGas', [[
            'from' => $fromAddress,
            'to' => $receiverAddress,
            'value' => $valueHex,
        ]]);

        $txHash = $this->rpcRequest('eth_sendTransaction', [[
            'from' => $fromAddress,
            'to' => $receiverAddress,
            'value' => $valueHex,
        ]]);

        $chainIdHex = $this->rpcRequest('eth_chainId');
        $chainId = $chainIdHex ? hexdec($chainIdHex) : null;

        return [
            'tx_hash' => $txHash,
            'wallet_address' => $fromAddress,
            'chain_id' => $chainId,
            'paid_amount_eth' => $payAmountEth,
        ];
    }

    /**
     * Tampilkan halaman checkout
     */
    public function checkout()
    {
        // Cek apakah user sudah login
        if (!Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk checkout.');
        }

        // Cart akan diambil dari localStorage via JavaScript
        return view('checkout');
    }

    /**
     * Proses order dan redirect ke pembayaran
     */
    public function processOrder(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'no_telp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'cart' => 'required|json' // Cart dikirim sebagai JSON dari frontend
        ]);

        try {
            DB::beginTransaction();

            $userId = Session::get('user_id');
            $cart = json_decode($validated['cart'], true);

            if (empty($cart)) {
                return back()->with('error', 'Keranjang belanja kosong.');
            }

            // Hitung total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Cek apakah customer sudah ada
            $customer = DB::table('customer')->where('user_id', $userId)->first();

            if ($customer) {
                // Update data customer
                DB::table('customer')
                    ->where('customer_id', $customer->customer_id)
                    ->update([
                        'nama' => $validated['nama'],
                        'no_telp' => $validated['no_telp'],
                        'alamat' => $validated['alamat'],
                        'updated_at' => now()
                    ]);
                $customerId = $customer->customer_id;
            } else {
                // Insert customer baru
                $customerId = DB::table('customer')->insertGetId([
                    'user_id' => $userId,
                    'nama' => $validated['nama'],
                    'no_telp' => $validated['no_telp'],
                    'alamat' => $validated['alamat'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert order
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $customerId,
                'order_date' => now(),
                'total_price' => $total,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert order details dan kurangi inventory
            foreach ($cart as $item) {
                // Cek stok yang tersedia
                $inventory = DB::table('inventory')
                    ->where('product_id', $item['id'])
                    ->first();

                if (!$inventory) {
                    throw new \Exception('Produk dengan ID ' . $item['id'] . ' tidak ditemukan di inventory.');
                }

                if ($inventory->stock < $item['quantity']) {
                    throw new \Exception('Stok untuk produk ID ' . $item['id'] . ' tidak cukup. Tersedia: ' . $inventory->stock . ', diminta: ' . $item['quantity']);
                }

                // Insert order detail
                DB::table('ordersdetail')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Kurangi stok di inventory
                DB::table('inventory')
                    ->where('product_id', $item['id'])
                    ->decrement('stock', $item['quantity']);

                // Update last_updated
                DB::table('inventory')
                    ->where('product_id', $item['id'])
                    ->update(['last_updated' => now()]);
            }

            DB::commit();

            // Simpan order_id ke session
            Session::put('current_order_id', $orderId);
            Session::forget('cart'); // Hapus cart

            return redirect()->route('payment', $orderId);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Halaman pembayaran
     */
    public function payment(int $orderId)
    {
        $order = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->select(['orders.*', 'customer.nama', 'customer.no_telp', 'customer.alamat'])
            ->where('orders.order_id', $orderId)
            ->first();

        if (!$order) {
            return redirect()->route('menu')->with('error', 'Pesanan tidak ditemukan.');
        }

        $orderDetails = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->select(['ordersdetail.*', 'products.product_name', 'products.image_url'])
            ->where('ordersdetail.order_id', $orderId)
            ->get();

        $expectedChainId = (int) env('CHAIN_ID', 1337);
        $receiverAddress = env('WEB3_RECEIVER_ADDRESS');
        $rpcUrl = env('WEB3_RPC_URL', 'http://127.0.0.1:7545');
        $idrPerEth = (float) env('WEB3_IDR_PER_ETH', 50000000);
        $idrPerEth = $idrPerEth > 0 ? $idrPerEth : 50000000;
        $payAmountEth = round(((float) $order->total_price) / $idrPerEth, 8);

        return view('payment', compact(
            'order',
            'orderDetails',
            'expectedChainId',
            'receiverAddress',
            'rpcUrl',
            'payAmountEth',
            'idrPerEth'
        ));
    }

    /**
     * Konfirmasi pembayaran
     */
    public function confirmPayment(Request $request, int $orderId)
    {
        $order = DB::table('orders')->where('order_id', $orderId)->first();
        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $idrPerEth = (float) env('WEB3_IDR_PER_ETH', 50000000);
        $idrPerEth = $idrPerEth > 0 ? $idrPerEth : 50000000;
        $payAmountEth = round(((float) $order->total_price) / $idrPerEth, 8);

        $validated = $request->validate([
            'payment_method' => 'required|in:qris,transfer,crypto,crypto_ganache',
            'payment_proof' => ['nullable', 'required_if:payment_method,qris', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'tx_hash' => ['nullable', 'required_if:payment_method,crypto', 'regex:/^0x[a-fA-F0-9]{64}$/'],
            'wallet_address' => ['nullable', 'required_if:payment_method,crypto', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'chain_id' => ['nullable', 'required_if:payment_method,crypto', 'integer', 'min:1'],
            'paid_amount_eth' => ['nullable', 'required_if:payment_method,crypto', 'numeric', 'min:0.00000001'],
        ]);

        try {
            $proofPath = null;

            // Handle QRIS payment proof upload
            if ($validated['payment_method'] === 'qris' && $request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $fileName = 'proof_' . $orderId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $proofPath = $file->storeAs('payment_proofs', $fileName, 'local');
            }

            // Handle Crypto Ganache payment
            if (($validated['payment_method'] ?? null) === 'crypto_ganache') {
                $ganachePayment = $this->processGanacheDirectPayment($payAmountEth);
                $validated['tx_hash'] = $ganachePayment['tx_hash'];
                $validated['wallet_address'] = $ganachePayment['wallet_address'];
                $validated['chain_id'] = $ganachePayment['chain_id'];
                $validated['paid_amount_eth'] = $ganachePayment['paid_amount_eth'];
            }

            // Validate Crypto chain ID
            if (($validated['payment_method'] ?? null) === 'crypto') {
                $expectedChainId = (int) env('CHAIN_ID', 1337);

                if ((int) $validated['chain_id'] !== $expectedChainId) {
                    return back()->with('error', 'Chain ID tidak sesuai dengan jaringan aplikasi.')->withInput();
                }
            }

            // Determine order status based on payment method
            $orderStatus = 'sedang dibuat'; // Default for transfer and crypto
            if ($validated['payment_method'] === 'qris') {
                $orderStatus = 'pending'; // Waiting for admin verification
            }

            // Update order dengan payment details
            $updateData = [
                'status' => $orderStatus,
                'payment_method' => $validated['payment_method'],
                'updated_at' => now()
            ];

            // Add crypto-specific fields if applicable
            if ($validated['payment_method'] === 'crypto' || $validated['payment_method'] === 'crypto_ganache') {
                $updateData['tx_hash'] = $validated['tx_hash'] ?? null;
                $updateData['wallet_address'] = $validated['wallet_address'] ?? null;
                $updateData['chain_id'] = $validated['chain_id'] ?? null;
                $updateData['paid_amount_eth'] = $validated['paid_amount_eth'] ?? null;
            }

            // Add QRIS-specific fields if applicable
            if ($validated['payment_method'] === 'qris') {
                $updateData['payment_proof_path'] = $proofPath;
            }

            DB::table('orders')
                ->where('order_id', $orderId)
                ->update($updateData);

            Session::forget('current_order_id');

            $successMessage = 'Pembayaran berhasil!';
            if ($validated['payment_method'] === 'qris') {
                $successMessage = 'Bukti pembayaran berhasil dikirim! Admin akan memverifikasi pembayaran Anda segera.';
            } else {
                $successMessage = 'Pembayaran berhasil! Pesanan Anda sedang diproses.';
            }

            return redirect()->route('order.status', $orderId)->with('success', $successMessage);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman status order user
     */
    public function orderStatus(int $orderId)
    {
        $order = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->select(['orders.*', 'customer.nama', 'customer.no_telp', 'customer.alamat'])
            ->where('orders.order_id', $orderId)
            ->first();

        if (!$order) {
            return redirect()->route('menu')->with('error', 'Pesanan tidak ditemukan.');
        }

        $orderDetails = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->select(['ordersdetail.*', 'products.product_name', 'products.image_url'])
            ->where('ordersdetail.order_id', $orderId)
            ->get();

        return view('order-status', compact('order', 'orderDetails'));
    }

    /**
     * Daftar pesanan user
     */
    public function myOrders()
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = Session::get('user_id');
        
        $orders = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->select(['orders.*', 'customer.nama'])
            ->where('customer.user_id', $userId)
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('my-orders', compact('orders'));
    }
}
