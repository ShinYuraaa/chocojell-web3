# Blockchain Order Recording - Quick Start Checklist

## ✅ Setup Checklist

### 1. Database Migration
```bash
php artisan migrate
# Output: Migrating: 2026_05_31_000001_add_blockchain_columns_to_orders_table
# Output: Migrated: 2026_05_31_000001_add_blockchain_columns_to_orders_table
```
- [ ] Migration berhasil berjalan
- [ ] Check: `php artisan tinker` → `DB::table('orders')->first();` ada column `blockchain_hash`

### 2. Start Ganache
```bash
ganache-cli --port 7545 --network-id 1337
# Atau buka Ganache UI dan setup network ke port 7545
```
- [ ] Ganache running di port 7545
- [ ] Catat akun pertama (akan jadi GANACHE_FROM_ADDRESS)
  ```
  Contoh: 0x627306090abab3a6e1400e9345bc60c40c335ae9
  ```

### 3. Configure .env
```env
# Paste akun pertama dari Ganache
GANACHE_FROM_ADDRESS=0x627306090abab3a6e1400e9345bc60c40c335ae9
WEB3_RPC_URL=http://127.0.0.1:7545
CHAIN_ID=1337
PRIVATE_KEY=<private key dari Ganache account pertama>
CONTRACT_ADDRESS=<akan diisi setelah deploy>
```
- [ ] Update GANACHE_FROM_ADDRESS
- [ ] Verify WEB3_RPC_URL = http://127.0.0.1:7545
- [ ] Verify CHAIN_ID = 1337

### 4. Compile Smart Contract
```bash
npm run truffle:compile
# Output: ✓ Compiled successfully
```
- [ ] Compile berhasil
- [ ] Check: `build/contracts/OrderRecord.json` ada

### 5. Deploy Smart Contract
```bash
npm run truffle:migrate
# Output: OrderRecord deployed to: 0x303d9c59330Ce7ca503A091Ce86F771E35d180f6
```
- [ ] Deployment berhasil
- [ ] Catat contract address: `0x...`
- [ ] Copy ke .env: `CONTRACT_ADDRESS=0x...`

### 6. Test Ganache Connection
```bash
# In terminal
curl -X POST http://127.0.0.1:7545 \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"eth_accounts","params":[],"id":1}'

# Should return: {"jsonrpc":"2.0","result":["0x627306090abab3a6e1400e9345bc60c40c335ae9",...],"id":1}
```
- [ ] Ganache RPC responds correctly
- [ ] Accounts list match dengan Ganache

### 7. Verify Configuration
```bash
php artisan tinker
>>> env('GANACHE_FROM_ADDRESS')
>>> env('CONTRACT_ADDRESS')
>>> env('WEB3_RPC_URL')
```
- [ ] GANACHE_FROM_ADDRESS = 0x627306...
- [ ] CONTRACT_ADDRESS = 0x303d9c...
- [ ] WEB3_RPC_URL = http://127.0.0.1:7545

---

## 🧪 Testing

### Test 1: Create Order
```bash
# Create test user dan order via UI atau API
# Atau gunakan: resources/views/checkout.blade.php
```
- [ ] Order berhasil dibuat
- [ ] Status order = pending

### Test 2: Check Blockchain Recording
```bash
php artisan tinker

# Check order di database
>>> $order = DB::table('orders')->orderBy('order_id', 'desc')->first();
>>> echo $order->blockchain_status;  // Should be: 'recorded' atau 'pending'
>>> echo $order->blockchain_hash;     // Should be: 0x...
>>> echo $order->blockchain_tx_hash;  // Should be: 0x... (jika recorded)
```
- [ ] blockchain_hash terisi (0x...)
- [ ] blockchain_status = 'recorded'
- [ ] blockchain_tx_hash terisi (optional, jika recorded)

### Test 3: Verify di Blockchain
```bash
# Option A: Ganache Console
truffle console --network development

> const OrderRecord = artifacts.require('OrderRecord');
> let instance = await OrderRecord.deployed();
> let hash = await instance.getOrderHash(1);  // orderId = 1
> console.log(hash);  // Should match blockchain_hash from DB
```

OR

```bash
# Option B: Curl to Ganache RPC (Advanced)
curl -X POST http://127.0.0.1:7545 \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "eth_call",
    "params": [{
      "to": "0x303d9c59330Ce7ca503A091Ce86F771E35d180f6",
      "data": "0xdf69558c0000000000000000000000000000000000000000000000000000000000000001"
    }, "latest"],
    "id": 1
  }'
```
- [ ] blockchain_hash di DB = hash di blockchain

### Test 4: Retry Failed Orders
```bash
# Buat order dengan setting CONTRACT_ADDRESS ke invalid
# Order akan gagal dengan blockchain_status = 'pending'

# Fix CONTRACT_ADDRESS di .env

# Run retry command
php artisan blockchain:record-pending --max-retries=3 --limit=5 -v

# Output should show:
# ✓ Order ID 1 recorded successfully (TX: 0x...)
```
- [ ] Retry command berhasil
- [ ] blockchain_status berubah dari 'pending' → 'recorded'

---

## 📊 Monitoring

### Daily Check
```bash
# Check pending orders
php artisan tinker
>>> DB::table('orders')->where('blockchain_status', 'pending')->count();

# If > 0, run retry
php artisan blockchain:record-pending --limit=20
```
- [ ] Run retry command regular basis (daily via cron)

### Monitor Logs
```bash
tail -f storage/logs/laravel.log | grep -i blockchain
```
- [ ] Monitor untuk error messages

---

## 🛠️ Troubleshooting

### Problem: "CONTRACT_ADDRESS tidak dikonfigurasi"
**Solution:**
```bash
# 1. Jalankan deploy
npm run truffle:migrate

# 2. Copy address dari output
# 3. Update .env:
CONTRACT_ADDRESS=0x303d9c59330Ce7ca503A091Ce86F771E35d180f6

# 4. Verify
php artisan tinker
>>> env('CONTRACT_ADDRESS')
```

### Problem: "Gagal terhubung ke Ganache RPC"
**Solution:**
```bash
# 1. Check Ganache running
lsof -i :7545  # macOS/Linux
netstat -ano | findstr :7545  # Windows

# 2. If not running, start Ganache
ganache-cli --port 7545 --network-id 1337

# 3. Check .env
WEB3_RPC_URL=http://127.0.0.1:7545
```

### Problem: blockchain_status tetap "pending"
**Solution:**
```bash
# 1. Check logs
tail -f storage/logs/laravel.log

# 2. Verify contract address
php artisan tinker
>>> env('CONTRACT_ADDRESS')

# 3. Run retry dengan verbose
php artisan blockchain:record-pending -vvv

# 4. Check transaction status in Ganache UI
```

### Problem: "Akun Ganache tidak ditemukan"
**Solution:**
```bash
# 1. Set GANACHE_FROM_ADDRESS manually
# Get from Ganache UI → Accounts → copy address

# 2. Update .env
GANACHE_FROM_ADDRESS=0x627306090abab3a6e1400e9345bc60c40c335ae9
```

---

## 📋 Summary

**Files Created:**
- [x] Migration: `database/migrations/2026_05_31_000001_add_blockchain_columns_to_orders_table.php`
- [x] Helper: `app/Helpers/BlockchainHelper.php`
- [x] Command: `app/Console/Commands/RecordPendingOrders.php`
- [x] Smart Contract: `contracts/OrderRecord.sol`
- [x] Documentation: `BLOCKCHAIN_ORDER_RECORDING.md`
- [x] Testing Guide: `BLOCKCHAIN_TESTING.md`

**Features:**
- ✅ All orders auto-recorded to blockchain (after creation)
- ✅ Mandatory for all payment methods (QRIS, Transfer, Crypto)
- ✅ Hash format: keccak256(orderId, customerId, totalPrice, orderDate)
- ✅ Retry mechanism for failed recordings (max 3 attempts)
- ✅ Immutable audit trail for order tracking

**Next Steps:**
1. Run migration: `php artisan migrate`
2. Update .env with Ganache info
3. Deploy contract: `npm run truffle:migrate`
4. Create test order and verify
5. Setup cron for daily retry: `php artisan blockchain:record-pending --limit=20`
