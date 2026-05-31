# Blockchain Order Recording - Implementation Guide

## Overview
Fitur ini merekam semua ID order ke blockchain (Ethereum) sebagai immutable audit trail. Setiap order secara otomatis di-hash menggunakan Keccak256 dan disimpan ke smart contract OrderRecord.

## Komponen yang Dibuat

### 1. Database Migration
**File:** `database/migrations/2026_05_31_000001_add_blockchain_columns_to_orders_table.php`

Columns yang ditambahkan:
- `blockchain_hash` (string, 66 chars) - Keccak256 hash dari order
- `blockchain_tx_hash` (string, 66 chars) - Transaction hash dari blockchain
- `blockchain_recorded_at` (timestamp) - Waktu order di-record ke blockchain
- `blockchain_status` (enum: pending|recorded|failed) - Status recording
- `blockchain_retry_count` (tinyint) - Jumlah retry attempts

**Jalankan:**
```bash
php artisan migrate
```

### 2. BlockchainHelper Class
**File:** `app/Helpers/BlockchainHelper.php`

Menyediakan fungsi-fungsi:
- `generateOrderHashWeb3()` - Generate Keccak256 hash dari order data
- `recordOrderToBlockchain()` - Record order hash ke smart contract
- `verifyOrderHashOnChain()` - Verify hash di blockchain
- `getOrderHashFromChain()` - Retrieve hash dari blockchain

**Format Hash:**
```
keccak256(abi.encodePacked(orderId, customerId, totalPrice, orderDate))
```

### 3. RecordPendingOrders Command
**File:** `app/Console/Commands/RecordPendingOrders.php`

Command untuk retry order yang gagal di-record ke blockchain.

**Jalankan:**
```bash
# Record pending orders dengan max 3 retries, process 10 orders per run
php artisan blockchain:record-pending

# Custom options
php artisan blockchain:record-pending --max-retries=5 --limit=20
```

### 4. OrderController Update
Modified `app/Http/Controllers/OrderController.php`:
- Menambah use statement untuk `BlockchainHelper`
- Memanggil `recordOrderToBlockchainAsync()` setelah order dibuat
- Method `recordOrderToBlockchainAsync()` untuk handle blockchain recording

## Environment Configuration

### Setup Ganache
1. **Buka Ganache** (UI atau CLI):
   ```bash
   ganache-cli --host 127.0.0.1 --port 7545 --network-id 1337
   ```

2. **Catat akun pertama** (akan digunakan sebagai GANACHE_FROM_ADDRESS):
   ```
   Contoh: 0x627306090abab3a6e1400e9345bc60c40c335ae9
   ```

### Update .env

```env
# Blockchain Configuration
WEB3_RPC_URL=http://127.0.0.1:7545
CHAIN_ID=1337
PRIVATE_KEY=<dari Ganache akun pertama>
CONTRACT_ADDRESS=<address OrderRecord contract setelah deploy>
GANACHE_FROM_ADDRESS=<alamat wallet untuk send tx, contoh: 0x627306090abab3a6e1400e9345bc60c40c335ae9>
WEB3_RECEIVER_ADDRESS=0xf3fb04B1eaC5231FaacD1A28e41C9b9dC4633274
WEB3_IDR_PER_ETH=50000000
```

## Smart Contract Deployment

### Deploy OrderRecord Contract

1. **Compile contract:**
   ```bash
   npm run truffle:compile
   ```

2. **Deploy ke Ganache:**
   ```bash
   npm run truffle:migrate
   ```

3. **Catat contract address** dari output deployment dan masukkan ke .env sebagai `CONTRACT_ADDRESS`

Contoh output:
```
OrderRecord
============
   Deploying 'OrderRecord'
   ----------------------
   > transaction hash:    0x...
   > Blocks: 1        Seconds: 1
   > contract address:    0x303d9c59330Ce7ca503A091Ce86F771E35d180f6
   > block number:        4
   > block timestamp:     ...
```

## Flow Diagram

```
1. User membuat order
   ↓
2. OrderController::processOrder() dipanggil
   ↓
3. Order di-insert ke database (status: pending)
   ↓
4. recordOrderToBlockchainAsync() dipanggil
   ↓
5. BlockchainHelper::generateOrderHashWeb3()
   Hash: keccak256(orderId, customerId, totalPrice, orderDate)
   ↓
6. BlockchainHelper::recordOrderToBlockchain()
   - Call smart contract recordOrder()
   - Kirim tx ke blockchain
   ↓
7. Jika berhasil:
   - Update orders table: blockchain_status = 'recorded'
   - Simpan blockchain_hash dan blockchain_tx_hash
   ↓
8. Jika gagal:
   - Update orders table: blockchain_status = 'pending'
   - Increment blockchain_retry_count
   - Log error
   ↓
9. Untuk retry failed recordings:
   php artisan blockchain:record-pending
   - Query orders dengan blockchain_status = 'pending'
   - Ulangi step 5-8
```

## Testing

### 1. Test dengan Ganache UI

1. Buka Ganache UI (http://127.0.0.1:7545 atau http://localhost:8545)
2. Buat order via aplikasi
3. Lihat transaction di Ganache dashboard

### 2. Test dengan Command Line

```bash
# Check pending orders
php artisan tinker
>>> DB::table('orders')->where('blockchain_status', 'pending')->get();

# Manual record pending
php artisan blockchain:record-pending --max-retries=3 --limit=5

# View recorded orders
>>> DB::table('orders')->where('blockchain_status', 'recorded')->get();
```

### 3. Verify di Blockchain

```bash
# Di Ganache console atau truffle:
>>> const OrderRecord = artifacts.require('OrderRecord');
>>> let instance = await OrderRecord.deployed();
>>> let orderHash = await instance.getOrderHash(1); // order_id = 1
>>> console.log(orderHash);
```

## Troubleshooting

### Error: "CONTRACT_ADDRESS tidak dikonfigurasi"
**Solution:** 
- Pastikan deploy smart contract dulu: `npm run truffle:migrate`
- Copy contract address ke .env `CONTRACT_ADDRESS`

### Error: "Gagal terhubung ke Ganache RPC"
**Solution:**
- Pastikan Ganache running: `ganache-cli --port 7545`
- Cek WEB3_RPC_URL di .env

### Error: "Akun Ganache tidak ditemukan"
**Solution:**
- Ganache belum unlock accounts
- Set GANACHE_FROM_ADDRESS ke salah satu akun dari Ganache

### Order blockchain_status tetap 'pending'
**Solution:**
- Cek logs: `tail -f storage/logs/laravel.log`
- Jalankan retry command: `php artisan blockchain:record-pending -vvv`
- Pastikan GAS limit cukup (default 100000)

## Dependencies

Pastikan sudah install composer packages:

```bash
# Install web3.js compatibility (jika perlu custom hashing)
composer require kornrunner/keccak
```

## Notes

- Setiap order MANDATORY di-record ke blockchain
- Hanya order_id yang di-hash, tidak ada data sensitif
- Recording bersifat async, tidak blocking checkout flow
- Failed recordings dapat di-retry hingga 3x (configurable)
- Blockchain hash immutable untuk audit trail
