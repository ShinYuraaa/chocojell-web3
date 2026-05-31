# Testing Blockchain Order Recording

## Prerequisites
- Ganache running: `ganache-cli --port 7545 --network-id 1337`
- OrderRecord contract deployed
- .env configured dengan CONTRACT_ADDRESS dan GANACHE_FROM_ADDRESS

## Test Scenarios

### Scenario 1: Create Order dan Auto-Record ke Blockchain

**Step 1: Login/Create Account**
```bash
# Pastikan ada user dalam database
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

**Step 2: Checkout Order**
```bash
curl -X POST http://localhost:8000/api/orders/process \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=your_session_id" \
  -d '{
    "nama": "John Doe",
    "no_telp": "081234567890",
    "alamat": "Jl. Test No. 1",
    "cart": "[{\"id\": 1, \"name\": \"Product 1\", \"price\": 100000, \"quantity\": 2}]"
  }'
```

**Step 3: Verify Blockchain Recording**
```bash
# Query database
php artisan tinker
>>> DB::table('orders')->orderBy('order_id', 'desc')->first();
>>> // Cek: blockchain_status, blockchain_hash, blockchain_tx_hash
```

**Expected Result:**
- `blockchain_status` = 'recorded' atau 'pending'
- `blockchain_hash` = 0x... (66 chars)
- `blockchain_tx_hash` = 0x... (jika berhasil) atau null (jika pending)

### Scenario 2: Retry Failed Recordings

**Trigger gagal (optional - untuk testing retry):**
```bash
# Edit .env, ubah CONTRACT_ADDRESS ke invalid address
CONTRACT_ADDRESS=0x0000000000000000000000000000000000000000

# Buat order baru
# Order akan gagal dan blockchain_status = 'pending'
```

**Run retry command:**
```bash
# Fix .env dengan address yang benar
CONTRACT_ADDRESS=0x303d9c59330Ce7ca503A091Ce86F771E35d180f6

# Run retry command dengan verbose
php artisan blockchain:record-pending --max-retries=3 --limit=5 -vvv
```

**Expected Output:**
```
Processing pending orders (max retries: 3, limit: 5)...
✓ Order ID 1 recorded successfully (TX: 0x...)
✓ Order ID 2 recorded successfully (TX: 0x...)
Completed! Success: 2, Failed: 0
```

### Scenario 3: Verify Order Hash di Blockchain

**Gunakan Ganache Console atau Truffle:**
```javascript
const OrderRecord = artifacts.require('OrderRecord');
const Web3 = require('web3');
const web3 = new Web3('http://127.0.0.1:7545');

async function verify() {
    const instance = await OrderRecord.deployed();
    
    // Get order hash (contoh order_id = 1)
    const hash = await instance.getOrderHash(1);
    console.log('Order 1 Hash:', hash);
    
    // Get total recorded orders
    const total = await instance.getTotalRecordedOrders();
    console.log('Total Orders Recorded:', total.toString());
    
    // Get recorded timestamp
    const timestamp = await instance.getOrderTimestamp(1);
    console.log('Recorded At:', new Date(timestamp * 1000));
}

verify().catch(console.error);
```

### Scenario 4: Monitor Blockchain Activity

**Watch Ganache logs:**
```bash
# Lihat output Ganache ketika order di-record
# Setiap transaction akan ditampilkan:
# ✓ Transaction: 0x...
#   From: 0x...
#   To: 0x...
#   Value: 0
#   Data: 0x...
```

## Monitoring Commands

### Check Pending Orders
```bash
php artisan tinker
>>> DB::table('orders')
    ->where('blockchain_status', 'pending')
    ->select('order_id', 'blockchain_status', 'blockchain_retry_count')
    ->get();
```

### Check All Recorded Orders
```bash
php artisan tinker
>>> DB::table('orders')
    ->where('blockchain_status', 'recorded')
    ->select('order_id', 'blockchain_hash', 'blockchain_tx_hash', 'blockchain_recorded_at')
    ->get();
```

### Check Failed Orders
```bash
php artisan tinker
>>> DB::table('orders')
    ->where('blockchain_status', 'failed')
    ->select('order_id', 'blockchain_retry_count')
    ->get();
```

### View Recent Transactions
```bash
php artisan tinker
>>> DB::table('orders')
    ->orderBy('blockchain_recorded_at', 'desc')
    ->where('blockchain_status', 'recorded')
    ->limit(10)
    ->select('order_id', 'blockchain_tx_hash', 'blockchain_recorded_at')
    ->get();
```

## Debugging

### Enable Detailed Logging

Edit `config/logging.php` atau `.env`:
```env
LOG_LEVEL=debug
```

**View logs:**
```bash
tail -f storage/logs/laravel.log
```

### Manual Hash Generation Test

```bash
php artisan tinker
>>> use App\Helpers\BlockchainHelper;
>>> $hash = BlockchainHelper::generateOrderHashWeb3(1, 100, 500000.0, '2026-05-31');
>>> echo $hash;
// Output: 0x...
```

### Manual Blockchain Recording Test

```bash
php artisan tinker
>>> use App\Helpers\BlockchainHelper;
>>> $result = BlockchainHelper::recordOrderToBlockchain(
      1,        // orderId
      100,      // customerId
      500000.0, // totalPrice
      '0x...'   // orderHash
    );
>>> print_r($result);
// Output: Array ( [success] => 1 [tx_hash] => 0x... [error] => )
```

## Performance Tips

1. **Batch Recording:** Jalankan `blockchain:record-pending` secara periodic (cron job)
   ```bash
   # Edit crontab: crontab -e
   */5 * * * * cd /laragon/www/ChocoJell && php artisan blockchain:record-pending --limit=20 >> /dev/null 2>&1
   ```

2. **Async Processing:** Untuk production, gunakan Laravel Queue
   ```php
   // Dalam recordOrderToBlockchainAsync(), dispatch job:
   RecordOrderJob::dispatch($orderId)->onQueue('blockchain');
   ```

3. **Gas Optimization:** Adjust gas limit dan price di BlockchainHelper
   ```php
   // Set custom gas limit
   $gasLimit = '0x27100'; // ~160000 gas
   ```

## Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| blockchain_status tetap 'pending' | RPC timeout atau invalid contract | Check logs, fix CONTRACT_ADDRESS, retry dengan `blockchain:record-pending` |
| "Gagal terhubung ke Ganache RPC" | Ganache tidak running | Jalankan `ganache-cli --port 7545` |
| "Akun Ganache tidak ditemukan" | GANACHE_FROM_ADDRESS kosong | Set dari Ganache accounts[0] |
| "Contract address mengarah ke EOA" | Gas estimation error | Pastikan CONTRACT_ADDRESS adalah smart contract address |
| blockchain_retry_count terus increment | Persistent error | Analisis log, fix issue, reset retry count |

### Reset Retry Count (jika perlu)
```bash
php artisan tinker
>>> DB::table('orders')
    ->where('blockchain_status', 'pending')
    ->update(['blockchain_retry_count' => 0]);
```

## Validation Checklist

- [ ] Migration sudah berjalan
- [ ] .env CONFIG lengkap (CONTRACT_ADDRESS, GANACHE_FROM_ADDRESS, WEB3_RPC_URL)
- [ ] Ganache running dengan network_id 1337
- [ ] Smart contract OrderRecord deployed
- [ ] Test order berhasil dibuat
- [ ] blockchain_hash terisi dengan benar
- [ ] blockchain_tx_hash terisi setelah recorded
- [ ] Verify di Ganache UI atau console
