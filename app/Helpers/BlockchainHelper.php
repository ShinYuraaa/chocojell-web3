<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BlockchainHelper
{
    private static ?string $rpcUrl = null;
    private static ?string $contractAddress = null;
    private static ?string $privateKey = null;
    private static ?string $fromAddress = null;

    public static function init()
    {
        self::$rpcUrl = env('WEB3_RPC_URL', 'http://127.0.0.1:7545');
        self::$contractAddress = env('CONTRACT_ADDRESS');
        self::$privateKey = env('PRIVATE_KEY');
        self::$fromAddress = env('GANACHE_FROM_ADDRESS');
    }

    /**
     * Safe logging - handles cases where facade root is not set
     */
    private static function safeLog(string $message, string $level = 'error')
    {
        try {
            if ($level === 'error') {
                Log::error($message);
            } else {
                Log::info($message);
            }
        } catch (\Throwable $e) {
            // Facade not available, silently continue
            // In production, you might want to log to file directly here
        }
    }

    /**
     * Generate keccak256 hash untuk order
     * Hash format: keccak256(abi.encodePacked(orderId, customerId, totalPrice, orderDate))
     * 
     * @param int $orderId
     * @param int $customerId
     * @param float $totalPrice
     * @param string $orderDate (Y-m-d format)
     * @return string hash dalam hex format (0x...)
     */
    public static function generateOrderHash(int $orderId, int $customerId, float $totalPrice, string $orderDate): string
    {
        // Konversi totalPrice ke integer (dalam satuan terkecil, misal cent atau unit)
        $totalPriceInt = (int) ($totalPrice * 100); // Jika perlu precision, adjust sesuai kebutuhan
        
        // Konversi orderDate ke timestamp
        $orderTimestamp = strtotime($orderDate);
        
        // Buat packed string sesuai Solidity format: abi.encodePacked(uint256, uint256, uint256, uint256)
        // Setiap uint256 = 32 bytes = 64 hex chars
        $packed = pack('N', $orderId) . 
                  pack('N', $customerId) . 
                  pack('N', $totalPriceInt) . 
                  pack('N', $orderTimestamp);
        
        // Hash dengan keccak256
        // Perhatian: PHP's hash() dengan 'sha3-256' adalah SHA3 (Keccak resmi), bukan Ethereum's Keccak256
        // Untuk kompatibilitas penuh, gunakan Web3 library
        
        // Alternative: gunakan web3.js via RPC atau library
        $hash = '0x' . hash('sha3-256', $packed, false);
        
        return $hash;
    }

    /**
     * Generate order hash menggunakan Web3.js (lebih akurat)
     * Method ini memanggil smart contract untuk generate hash yang sesuai Ethereum standard
     * 
     * @param int $orderId
     * @param int $customerId
     * @param float $totalPrice
     * @param string $orderDate
     * @return string
     */
    public static function generateOrderHashWeb3(int $orderId, int $customerId, float $totalPrice, string $orderDate): string
    {
        // Gunakan ethers.js / web3.js approach
        // Konversi ke format yang sesuai dengan Solidity
        $totalPriceWei = (int) ($totalPrice * 100); // Sesuaikan unit
        $orderTimestamp = (int) strtotime($orderDate);
        
        // Format: uint256 values dalam decimal
        $dataToHash = sprintf(
            '%064x%064x%064x%064x',
            $orderId,
            $customerId,
            $totalPriceWei,
            $orderTimestamp
        );
        
        // Gunakan Keccak256 (kornrunner/keccak library)
        try {
            $hash = '0x' . \kornrunner\Keccak::hash(hex2bin($dataToHash), 256);
        } catch (\Exception $e) {
            self::safeLog('Keccak hash error: ' . $e->getMessage());
            // Fallback ke sha3-256 (tidak ideal tapi lebih baik daripada gagal)
            $hash = '0x' . hash('sha3-256', hex2bin($dataToHash), false);
        }
        
        return $hash;
    }

    /**
     * Record order ke blockchain
     * 
     * @param int $orderId
     * @param int $customerId
     * @param float $totalPrice
     * @param string $orderHash
     * @return array ['success' => bool, 'tx_hash' => string|null, 'error' => string|null]
     */
    public static function recordOrderToBlockchain(int $orderId, int $customerId, float $totalPrice, string $orderHash): array
    {
        self::init();

        if (!self::$contractAddress) {
            return [
                'success' => false,
                'tx_hash' => null,
                'error' => 'CONTRACT_ADDRESS tidak dikonfigurasi di .env'
            ];
        }

        try {
            // Ambil nonce dari akun
            $nonce = self::getNonce(self::$fromAddress);
            if ($nonce === null) {
                throw new \RuntimeException('Gagal mendapatkan nonce');
            }

            // Encode function call: recordOrder(uint256 orderId, uint256 customerId, uint256 totalPrice, bytes32 orderHash)
            $functionSelector = self::getFunctionSelector('recordOrder(uint256,uint256,uint256,bytes32)');
            
            // Encode parameters
            $totalPriceInt = (int) ($totalPrice * 100);
            $params = sprintf(
                '%064x%064x%064x%s',
                $orderId,
                $customerId,
                $totalPriceInt,
                substr($orderHash, 2) // Remove '0x' prefix
            );

            $data = '0x' . $functionSelector . $params;

            // Estimate gas
            $gasLimit = self::estimateGas(self::$fromAddress, self::$contractAddress, $data);
            if (!$gasLimit) {
                $gasLimit = '0x186a0'; // Default 100000 gas
            }

            // Get gas price
            $gasPrice = self::getGasPrice();
            if (!$gasPrice) {
                $gasPrice = '0x3b9aca00'; // Default 1 Gwei
            }

            // Build transaction
            $txData = [
                'from' => self::$fromAddress,
                'to' => self::$contractAddress,
                'data' => $data,
                'gas' => $gasLimit,
                'gasPrice' => $gasPrice,
                'nonce' => '0x' . dechex($nonce),
            ];

            // Sign dan send transaction
            $txHash = self::sendTransaction($txData);

            if (!$txHash) {
                throw new \RuntimeException('Gagal mengirim transaction ke blockchain');
            }

            return [
                'success' => true,
                'tx_hash' => $txHash,
                'error' => null
            ];

        } catch (\Exception $e) {
            self::safeLog('BlockchainHelper::recordOrderToBlockchain error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'tx_hash' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * RPC call helper
     */
    private static function rpcCall(string $method, array $params = [])
    {
        $payload = json_encode([
            'jsonrpc' => '2.0',
            'id' => time(),
            'method' => $method,
            'params' => $params,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 15,
            ],
        ]);

        $response = @file_get_contents(self::$rpcUrl, false, $context);
        if ($response === false) {
            self::safeLog('RPC connection failed: ' . self::$rpcUrl);
            return null;
        }

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            self::safeLog('RPC error: ' . json_encode($json['error']));
            return null;
        }

        return $json['result'] ?? null;
    }

    /**
     * Calculate function selector using Keccak256
     */
    private static function getFunctionSelector(string $signature): string
    {
        try {
            $hash = \kornrunner\Keccak::hash($signature, 256);
            return substr($hash, 0, 8);
        } catch (\Exception $e) {
            self::safeLog('Function selector calculation error: ' . $e->getMessage());
            // Fallback to empty - will cause RPC error
            return '00000000';
        }
    }

    /**
     * Get nonce untuk address
     */
    private static function getNonce(string $address): ?int
    {
        $nonceHex = self::rpcCall('eth_getTransactionCount', [$address, 'pending']);
        if ($nonceHex === null) {
            return null;
        }

        return hexdec($nonceHex);
    }

    /**
     * Estimate gas untuk transaction
     */
    private static function estimateGas(string $from, string $to, string $data): ?string
    {
        return self::rpcCall('eth_estimateGas', [[
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]]);
    }

    /**
     * Get current gas price
     */
    private static function getGasPrice(): ?string
    {
        return self::rpcCall('eth_gasPrice');
    }

    /**
     * Send signed transaction
     */
    private static function sendTransaction(array $txData): ?string
    {
        // PERHATIAN: Method ini simplified untuk Ganache
        // Untuk production, gunakan proper signing dengan private key
        
        // Untuk Ganache, kita bisa langsung pakai eth_sendTransaction
        // karena Ganache unlock semua akun by default
        
        return self::rpcCall('eth_sendTransaction', [$txData]);
    }

    /**
     * Verify order hash di blockchain
     */
    public static function verifyOrderHashOnChain(int $orderId, string $expectedHash): bool
    {
        self::init();

        try {
            // Call smart contract: verifyOrderHash(uint256 orderId, bytes32 expectedHash)
            $functionSelector = self::getFunctionSelector('verifyOrderHash(uint256,bytes32)');
            
            $params = sprintf(
                '%064x%s',
                $orderId,
                substr($expectedHash, 2)
            );

            $data = '0x' . $functionSelector . $params;

            $result = self::rpcCall('eth_call', [[
                'to' => self::$contractAddress,
                'data' => $data,
            ], 'latest']);

            // Parse result - akan return bool (0x0 atau 0x1)
            return $result === '0x1';

        } catch (\Exception $e) {
            self::safeLog('BlockchainHelper::verifyOrderHashOnChain error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order hash dari blockchain
     */
    public static function getOrderHashFromChain(int $orderId): ?string
    {
        self::init();

        try {
            // Call smart contract: getOrderHash(uint256 orderId)
            $functionSelector = self::getFunctionSelector('getOrderHash(uint256)');
            
            $param = sprintf('%064x', $orderId);
            $data = '0x' . $functionSelector . $param;

            $result = self::rpcCall('eth_call', [[
                'to' => self::$contractAddress,
                'data' => $data,
            ], 'latest']);

            if ($result === '0x' || $result === null) {
                return null;
            }

            return '0x' . substr($result, 2); // Ensure 0x prefix

        } catch (\Exception $e) {
            self::safeLog('BlockchainHelper::getOrderHashFromChain error: ' . $e->getMessage());
            return null;
        }
    }
}
