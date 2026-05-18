<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Choco Jell</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">
    <link rel="icon" href="{{ asset('img/logo.png') }}" />
    <style>
        .crypto-box {
            margin-top: 16px;
            padding: 14px;
            border: 1px solid #d3d3d3;
            border-radius: 10px;
            background: #f8ffff;
            display: none;
        }

        .crypto-box.active {
            display: block;
        }

        .qris-box {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #00888a;
            border-radius: 12px;
            background: #f0fffe;
            text-align: center;
            display: none;
        }

        .qris-box.active {
            display: block;
        }

        .qris-image {
            max-width: 300px;
            width: 100%;
            margin: 15px auto;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .qris-instruction {
            margin: 15px 0;
            padding: 12px;
            background: #fff;
            border-left: 4px solid #00888a;
            border-radius: 4px;
            text-align: left;
            font-size: 0.95rem;
        }

        .qris-instruction ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .qris-instruction li {
            margin: 8px 0;
        }

        .upload-proof {
            margin: 20px 0;
            padding: 20px;
            border: 2px dashed #00888a;
            border-radius: 12px;
            background: #f9fffe;
            display: none;
        }

        .upload-proof.active {
            display: block;
        }

        .upload-proof label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .upload-proof input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #00888a;
            border-radius: 8px;
            cursor: pointer;
        }

        .upload-proof input[type="file"]::file-selector-button {
            background: #00888a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .file-info {
            margin-top: 10px;
            font-size: 0.85rem;
            color: #666;
        }

        .preview-image {
            margin-top: 15px;
            max-width: 200px;
        }

        .preview-image img {
            max-width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .crypto-note {
            font-size: 0.9rem;
            color: #555;
            margin: 8px 0;
        }

        .btn-connect {
            background: #f6851b;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .wallet-info {
            margin-top: 8px;
            font-size: 0.9rem;
            color: #333;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <header class="navbar" style="position: fixed; top: 0; width: 100%; z-index: 1000; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
            <span>Choco Jell</span>
        </div>
    </header>

    <div class="payment-container">
        <div class="payment-section">
            <h2>Pembayaran - Order #{{ $order->order_id }}</h2>

            @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="order-summary">
                <h3 style="color: #00888a; margin-bottom: 15px;">Detail Pesanan</h3>
                <div class="summary-row">
                    <span>Nama Penerima:</span>
                    <strong>{{ $order->nama }}</strong>
                </div>
                <div class="summary-row">
                    <span>No. Telepon:</span>
                    <strong>{{ $order->no_telp }}</strong>
                </div>
                <div class="summary-row">
                    <span>Alamat:</span>
                    <strong>{{ $order->alamat }}</strong>
                </div>
                <div class="summary-row">
                    <span>Total Pembayaran:</span>
                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-row">
                    <span>Estimasi Crypto:</span>
                    <strong>{{ number_format($payAmountEth, 8, '.', '') }} ETH</strong>
                </div>
                <div class="crypto-note">Kurs simulasi lokal: 1 ETH = Rp {{ number_format($idrPerEth, 0, ',', '.') }}</div>
            </div>

            <div class="product-list">
                <h3 style="color: #00888a; margin: 20px 0 15px;">Items yang Dibeli:</h3>
                @foreach($orderDetails as $detail)
                <div class="product-item">
                    <img src="{{ asset($detail->image_url ?? 'img/logo.png') }}" alt="{{ $detail->product_name }}">
                    <div class="product-info">
                        <h4>{{ $detail->product_name }}</h4>
                        <p class="product-quantity">Jumlah: {{ $detail->quantity }}</p>
                        <p class="product-price">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <form action="{{ route('payment.confirm', $order->order_id) }}" method="POST" id="paymentForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tx_hash" id="txHash" value="{{ old('tx_hash') }}">
                <input type="hidden" name="wallet_address" id="walletAddress" value="{{ old('wallet_address') }}">
                <input type="hidden" name="chain_id" id="chainId" value="{{ old('chain_id') }}">
                <input type="hidden" name="paid_amount_eth" id="paidAmountEth" value="{{ old('paid_amount_eth', number_format($payAmountEth, 8, '.', '')) }}">

                <div class="payment-methods">
                    <h3 style="color: #00888a; margin: 30px 0 20px;">Pilih Metode Pembayaran:</h3>

                    <label class="payment-option" data-method="qris">
                        <input type="radio" name="payment_method" value="qris" required>
                        <div class="payment-icon"></div>
                        <div class="payment-details">
                            <h4>💳 QRIS</h4>
                            <p>Scan QRIS dengan aplikasi perbankan atau e-wallet</p>
                        </div>
                    </label>

                    <label class="payment-option" data-method="transfer">
                        <input type="radio" name="payment_method" value="transfer" required>
                        <div class="payment-icon"></div>
                        <div class="payment-details">
                            <h4>Transfer Bank</h4>
                            <p>BCA, Mandiri, BNI, BRI</p>
                        </div>
                    </label>

                    <label class="payment-option" data-method="crypto">
                        <input type="radio" name="payment_method" value="crypto" required>
                        <div class="payment-icon"></div>
                        <div class="payment-details">
                            <h4>Crypto (ETH - MetaMask)</h4>
                            <p>Bayar melalui jaringan Ganache chain {{ $expectedChainId }}</p>
                        </div>
                    </label>

                    <label class="payment-option" data-method="crypto_ganache">
                        <input type="radio" name="payment_method" value="crypto_ganache" required>
                        <div class="payment-icon"></div>
                        <div class="payment-details">
                            <h4>Crypto Test (Ganache langsung)</h4>
                            <p>Tanpa MetaMask, transaksi dikirim server untuk ujicoba lokal</p>
                        </div>
                    </label>

                    <!-- QRIS Box -->
                    <div class="qris-box" id="qrisBox">
                        <h3 style="color: #00888a; margin-bottom: 15px;">💳 Scan QRIS Untuk Pembayaran</h3>
                        <img src="{{ asset('img/qris.jpeg') }}" alt="QRIS Code" class="qris-image">
                        
                        <div class="qris-instruction">
                            <strong>Cara Membayar:</strong>
                            <ol>
                                <li>Buka aplikasi perbankan atau e-wallet Anda (GCash, GoPay, OVO, dll)</li>
                                <li>Pilih menu "Scan QRIS" atau "Bayar QRIS"</li>
                                <li>Arahkan kamera ke kode QRIS di atas</li>
                                <li>Masukkan nominal: <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></li>
                                <li>Selesaikan pembayaran dan screenshot bukti pembayaran</li>
                                <li>Upload screenshot bukti pembayaran di bawah</li>
                            </ol>
                        </div>

                        <div class="upload-proof active" id="uploadProof">
                            <label for="payment_proof">📸 Upload Bukti Pembayaran (Screenshot)</label>
                            <input type="file" name="payment_proof" id="paymentProof" accept="image/*">
                            <div class="file-info">
                                Format yang diterima: JPG, PNG, GIF, WebP (Maksimal 5 MB)
                            </div>
                            <div id="previewContainer" class="preview-image" style="display: none;">
                                <img id="previewImage" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <!-- Crypto Box -->
                    <div class="crypto-box" id="cryptoBox">
                        <button type="button" class="btn-connect" id="btnConnectWallet">Connect MetaMask</button>
                        <button type="button" class="btn-connect" id="btnSwitchNetwork" style="margin-left: 8px; background: #00888a;">Switch ke Ganache</button>
                        <div class="wallet-info" id="walletInfo">Wallet belum terhubung.</div>
                        <div class="wallet-info" id="chainInfo">Chain belum terdeteksi.</div>
                        <div class="crypto-note">Tujuan wallet: {{ $receiverAddress ?: 'otomatis pakai akun Ganache lain' }}</div>
                        <div class="crypto-note">Jika ingin tanpa MetaMask, pilih Crypto Test (Ganache langsung).</div>
                        <div class="crypto-note">Catatan: untuk mode Ganache langsung, gunakan wallet address Ganache, bukan contract address.</div>
                    </div>
                </div>

                <button type="submit" class="btn-pay" id="btnPay" disabled>
                    Lanjutkan Pembayaran
                </button>
            </form>

            <p style="text-align: center; color: #999; margin-top: 20px; font-size: 0.9rem;">
                Pembayaran Anda aman dan terenkripsi
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/web3@4.16.0/dist/web3.min.js"></script>
    <script>
        const EXPECTED_CHAIN_ID = Number({{ $expectedChainId }});
        const EXPECTED_CHAIN_ID_HEX = '0x' + EXPECTED_CHAIN_ID.toString(16);
        const RECEIVER_ADDRESS = "{{ $receiverAddress }}";
        const RPC_URL = "{{ $rpcUrl }}";
        const AMOUNT_ETH = "{{ number_format($payAmountEth, 8, '.', '') }}";

        const paymentOptions = document.querySelectorAll('.payment-option');
        const btnPay = document.getElementById('btnPay');
        const paymentForm = document.getElementById('paymentForm');
        const qrisBox = document.getElementById('qrisBox');
        const cryptoBox = document.getElementById('cryptoBox');
        const walletInfo = document.getElementById('walletInfo');
        const chainInfo = document.getElementById('chainInfo');
        const proofInput = document.getElementById('paymentProof');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        let selectedMethod = null;
        let connectedWallet = null;

        // Image preview untuk QRIS
        proofInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 5 MB');
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });

        function setChainInfoText(text) {
            chainInfo.textContent = text;
        }

        async function detectChainId() {
            if (!window.ethereum) return null;
            try {
                const chainIdHex = await window.ethereum.request({ method: 'eth_chainId' });
                if (chainIdHex) return parseInt(chainIdHex, 16);
            } catch (error) {}
            try {
                const netVersion = await window.ethereum.request({ method: 'net_version' });
                if (netVersion) return parseInt(netVersion, 10);
            } catch (error) {}
            return null;
        }

        async function switchToGanacheNetwork() {
            if (typeof window.ethereum === 'undefined') {
                alert('MetaMask tidak ditemukan. Silakan install MetaMask terlebih dahulu.');
                return;
            }

            try {
                await window.ethereum.request({
                    method: 'wallet_switchEthereumChain',
                    params: [{ chainId: EXPECTED_CHAIN_ID_HEX }]
                });
            } catch (switchError) {
                if (switchError && switchError.code === 4902) {
                    await window.ethereum.request({
                        method: 'wallet_addEthereumChain',
                        params: [{
                            chainId: EXPECTED_CHAIN_ID_HEX,
                            chainName: 'Ganache Local',
                            rpcUrls: [RPC_URL],
                            nativeCurrency: {
                                name: 'Ether',
                                symbol: 'ETH',
                                decimals: 18
                            }
                        }]
                    });
                } else {
                    throw switchError;
                }
            }

            const chainId = await detectChainId();
            if (chainId) {
                document.getElementById('chainId').value = chainId;
                setChainInfoText('Chain aktif: ' + chainId + ' (expected: ' + EXPECTED_CHAIN_ID + ')');
            }
        }

        paymentOptions.forEach((option) => {
            option.addEventListener('click', function () {
                paymentOptions.forEach((el) => el.classList.remove('selected'));
                this.classList.add('selected');

                const input = this.querySelector('input[name="payment_method"]');
                input.checked = true;
                selectedMethod = input.value;

                // Show/hide QRIS box
                if (selectedMethod === 'qris') {
                    qrisBox.classList.add('active');
                    cryptoBox.classList.remove('active');
                    btnPay.disabled = false;
                    return;
                }

                // Show/hide Crypto box
                if (selectedMethod === 'crypto' || selectedMethod === 'crypto_ganache') {
                    cryptoBox.classList.add('active');
                    qrisBox.classList.remove('active');
                    btnPay.disabled = false;
                    return;
                }

                // Transfer bank - no special box
                qrisBox.classList.remove('active');
                cryptoBox.classList.remove('active');
                btnPay.disabled = false;
            });
        });

        async function connectWallet() {
            if (typeof window.ethereum === 'undefined') {
                alert('MetaMask tidak ditemukan. Silakan install MetaMask terlebih dahulu.');
                return;
            }

            try {
                const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                const chainId = await detectChainId();

                if (!chainId) {
                    setChainInfoText('Chain ID tidak terbaca dari MetaMask. Coba klik Switch ke Ganache.');
                    alert('Chain ID tidak terbaca. Klik tombol Switch ke Ganache lalu Connect lagi.');
                    return;
                }

                setChainInfoText('Chain aktif: ' + chainId + ' (expected: ' + EXPECTED_CHAIN_ID + ')');

                if (chainId !== EXPECTED_CHAIN_ID) {
                    alert('Network MetaMask tidak sesuai. Chain aktif ' + chainId + ', expected ' + EXPECTED_CHAIN_ID + '. Klik Switch ke Ganache.');
                    return;
                }

                connectedWallet = accounts[0];
                document.getElementById('walletAddress').value = connectedWallet;
                document.getElementById('chainId').value = chainId;
                document.getElementById('paidAmountEth').value = AMOUNT_ETH;
                walletInfo.textContent = 'Wallet terhubung: ' + connectedWallet;
            } catch (error) {
                alert('Gagal connect wallet: ' + (error?.message || 'unknown error'));
            }
        }

        async function sendCryptoPayment() {
            if (!connectedWallet) {
                throw new Error('Wallet belum terhubung. Klik Connect MetaMask dulu.');
            }

            if (!RECEIVER_ADDRESS) {
                throw new Error('Wallet penerima belum diatur. Tambahkan WEB3_RECEIVER_ADDRESS pada .env');
            }

            const valueHex = "0x" + BigInt(Math.floor(Number(AMOUNT_ETH) * 1e18)).toString(16);

            const txHash = await window.ethereum.request({
                method: 'eth_sendTransaction',
                params: [{
                    from: connectedWallet,
                    to: RECEIVER_ADDRESS,
                    value: valueHex
                }]
            });

            document.getElementById('txHash').value = txHash;
            return txHash;
        }

        document.getElementById('btnConnectWallet').addEventListener('click', connectWallet);
        document.getElementById('btnSwitchNetwork').addEventListener('click', async () => {
            try {
                await switchToGanacheNetwork();
                alert('Berhasil switch network. Silakan klik Connect MetaMask.');
            } catch (error) {
                alert('Gagal switch network: ' + (error?.message || 'unknown error'));
            }
        });

        if (window.ethereum) {
            window.ethereum.on('chainChanged', async () => {
                const chainId = await detectChainId();
                if (chainId) {
                    document.getElementById('chainId').value = chainId;
                    setChainInfoText('Chain aktif: ' + chainId + ' (expected: ' + EXPECTED_CHAIN_ID + ')');
                }
            });
        }

        paymentForm.addEventListener('submit', async function (e) {
            // Untuk QRIS - validasi file upload
            if (selectedMethod === 'qris') {
                if (!proofInput.files[0]) {
                    e.preventDefault();
                    alert('Silakan upload bukti pembayaran terlebih dahulu!');
                    return;
                }
                btnPay.disabled = true;
                btnPay.textContent = 'Mengirim bukti pembayaran...';
                return;
            }

            // Untuk Transfer bank - langsung submit
            if (selectedMethod === 'transfer') {
                return;
            }

            // Untuk Crypto Test (Ganache langsung) - langsung submit, backend handle semua
            if (selectedMethod === 'crypto_ganache') {
                return;
            }

            // Untuk Crypto (MetaMask) - proses wallet payment
            if (selectedMethod === 'crypto') {
                e.preventDefault();
                btnPay.innerHTML = 'Memproses Pembayaran Crypto...';
                btnPay.disabled = true;

                try {
                    await sendCryptoPayment();
                    paymentForm.submit();
                } catch (error) {
                    alert(error.message || 'Pembayaran crypto gagal diproses.');
                    btnPay.innerHTML = 'Lanjutkan Pembayaran';
                    btnPay.disabled = false;
                }
            }
        });
    </script>
</body>
</html>
