#!/bin/bash
# Quick Setup Guide for Blockchain Order Recording

echo "================================"
echo "Blockchain Order Recording Setup"
echo "================================"
echo ""

# Step 1: Run Migration
echo "Step 1: Running database migration..."
php artisan migrate
if [ $? -ne 0 ]; then
    echo "❌ Migration failed! Check your database connection."
    exit 1
fi
echo "✓ Migration completed"
echo ""

# Step 2: Check Ganache
echo "Step 2: Checking Ganache connection..."
curl -s -X POST http://127.0.0.1:7545 \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"eth_accounts","params":[],"id":1}' | grep -q "0x"

if [ $? -ne 0 ]; then
    echo "⚠️  Ganache tidak ditemukan di http://127.0.0.1:7545"
    echo "   Jalankan: ganache-cli --port 7545 --network-id 1337"
    echo ""
fi

# Step 3: Check .env configuration
echo "Step 3: Checking .env configuration..."
if grep -q "CONTRACT_ADDRESS=" .env; then
    CONTRACT=$(grep "CONTRACT_ADDRESS=" .env | cut -d'=' -f2)
    if [ -z "$CONTRACT" ] || [ "$CONTRACT" = "" ]; then
        echo "⚠️  CONTRACT_ADDRESS masih kosong di .env"
        echo "   Jalankan: npm run truffle:migrate"
        echo "   Kemudian copy contract address ke .env"
    else
        echo "✓ CONTRACT_ADDRESS configured: $CONTRACT"
    fi
fi

if grep -q "GANACHE_FROM_ADDRESS=" .env; then
    FROM_ADDR=$(grep "GANACHE_FROM_ADDRESS=" .env | cut -d'=' -f2)
    if [ -z "$FROM_ADDR" ] || [ "$FROM_ADDR" = "" ]; then
        echo "⚠️  GANACHE_FROM_ADDRESS masih kosong di .env"
        echo "   Set dengan: Ganache accounts[0]"
    else
        echo "✓ GANACHE_FROM_ADDRESS configured"
    fi
fi

echo ""
echo "================================"
echo "Setup Complete!"
echo "================================"
echo ""
echo "Next steps:"
echo "1. Pastikan Ganache running: ganache-cli --port 7545"
echo "2. Deploy contract: npm run truffle:migrate"
echo "3. Update .env dengan CONTRACT_ADDRESS dan GANACHE_FROM_ADDRESS"
echo "4. Buat order untuk test blockchain recording"
echo "5. Monitor: php artisan blockchain:record-pending -v"
echo ""
