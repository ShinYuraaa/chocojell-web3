<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class AdminController extends Controller
{
    // Halaman Login Admin
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (Session::has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    // Process Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $admin = DB::table('admin')->where('email', '=', $request->input('email'), 'and')->first();

        if ($admin && Hash::check($request->input('password'), $admin->password)) {
            // Login berhasil, simpan session
            Session::put('admin_id', $admin->admin_id);
            Session::put('admin_name', $admin->nama);
            Session::put('admin_email', $admin->email);

            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang, ' . $admin->nama);
        }

        return back()->with('error', 'Email atau password salah!')->withInput();
    }

    // Logout Admin
    public function logout()
    {
        Session::forget('admin_id');
        Session::forget('admin_name');
        Session::forget('admin_email');
        Session::flush();

        return redirect()->route('admin.login')->with('success', 'Berhasil logout.');
    }

    public function dashboard()
    {
        $totalProducts = DB::table('products')->count();
        
        // Total orders exclude cancelled
        $totalOrders = DB::table('orders')->where('status', '!=', 'dibatalkan')->count();
        
        // Breakdown by status
        $pendingOrders = DB::table('orders')->where('status', 'pending')->count();
        $cancelledOrders = DB::table('orders')->where('status', 'dibatalkan')->count();
        
        // Completed today
        $completedToday = DB::table('orders')
            ->where('status', 'selesai')
            ->whereDate('updated_at', DB::raw('CURDATE()'))
            ->count();
        
        $recentOrders = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->select([
                'orders.*',
                DB::raw('COALESCE(orders.customer_name, customer.nama) as customer_name')
            ])
            ->orderBy('orders.created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Top products today
        $topProductsToday = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->join('orders', 'ordersdetail.order_id', '=', 'orders.order_id')
            ->whereDate('orders.created_at', DB::raw('CURDATE()'))
            ->where('orders.status', '!=', 'dibatalkan')
            ->select('products.product_id', 'products.product_name', 'products.image_url', DB::raw('SUM(ordersdetail.quantity) as total_sold'))
            ->groupBy('products.product_id', 'products.product_name', 'products.image_url')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Top products this week
        $topProductsWeek = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->join('orders', 'ordersdetail.order_id', '=', 'orders.order_id')
            ->whereRaw('WEEK(orders.created_at) = WEEK(NOW())')
            ->whereRaw('YEAR(orders.created_at) = YEAR(NOW())')
            ->where('orders.status', '!=', 'dibatalkan')
            ->select('products.product_id', 'products.product_name', 'products.image_url', DB::raw('SUM(ordersdetail.quantity) as total_sold'))
            ->groupBy('products.product_id', 'products.product_name', 'products.image_url')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Top products this month
        $topProductsMonth = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->join('orders', 'ordersdetail.order_id', '=', 'orders.order_id')
            ->whereMonth('orders.created_at', DB::raw('MONTH(NOW())'))
            ->whereYear('orders.created_at', DB::raw('YEAR(NOW())'))
            ->where('orders.status', '!=', 'dibatalkan')
            ->select('products.product_id', 'products.product_name', 'products.image_url', DB::raw('SUM(ordersdetail.quantity) as total_sold'))
            ->groupBy('products.product_id', 'products.product_name', 'products.image_url')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Top products this year
        $topProductsYear = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->join('orders', 'ordersdetail.order_id', '=', 'orders.order_id')
            ->whereYear('orders.created_at', DB::raw('YEAR(NOW())'))
            ->where('orders.status', '!=', 'dibatalkan')
            ->select('products.product_id', 'products.product_name', 'products.image_url', DB::raw('SUM(ordersdetail.quantity) as total_sold'))
            ->groupBy('products.product_id', 'products.product_name', 'products.image_url')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts', 'totalOrders', 'pendingOrders', 'cancelledOrders', 
            'completedToday', 'recentOrders', 'topProductsToday', 'topProductsWeek', 
            'topProductsMonth', 'topProductsYear'
        ));
    }

    public function products()
    {
        $products = DB::table('products')
            ->join('inventory', 'products.product_id', '=', 'inventory.product_id')
            ->select(['products.*', 'inventory.stock'])
            ->get();

        return view('admin.products', compact('products'));
    }

    public function createProduct()
    {
        return view('admin.product-create');
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'stock' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Upload gambar jika ada
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('img/products'), $imageName);
                $imagePath = 'img/products/' . $imageName;
            }

            // Insert product
            $productId = DB::table('products')->insertGetId([
                'product_name' => $validated['product_name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'category' => $validated['category'],
                'image_url' => $imagePath,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert inventory
            DB::table('inventory')->insert([
                'product_id' => $productId,
                'stock' => $validated['stock'],
                'last_updated' => now()
            ]);

            DB::commit();

            return redirect()->route('admin.products')->with('success', 'Produk berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function editProduct(int $id)
    {
        $product = DB::table('products')
            ->join('inventory', 'products.product_id', '=', 'inventory.product_id')
            ->select(['products.*', 'inventory.stock', 'inventory.inventory_id'])
            ->where('products.product_id', $id)
            ->first();

        if (!$product) {
            return redirect()->route('admin.products')->with('error', 'Produk tidak ditemukan');
        }

        return view('admin.product-edit', compact('product'));
    }

    public function updateProduct(Request $request, int $id)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'stock' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'product_name' => $validated['product_name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'category' => $validated['category'],
                'updated_at' => now()
            ];

            // Upload gambar baru jika ada
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('img/products'), $imageName);
                $updateData['image_url'] = 'img/products/' . $imageName;
            }

            // Update product
            DB::table('products')
                ->where('product_id', $id)
                ->update($updateData);

            // Update inventory
            DB::table('inventory')
                ->where('product_id', $id)
                ->update([
                    'stock' => $validated['stock'],
                    'last_updated' => now()
                ]);

            DB::commit();

            return redirect()->route('admin.products')->with('success', 'Produk berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate produk: ' . $e->getMessage());
        }
    }

    public function deleteProduct(int $id)
    {
        try {
            DB::beginTransaction();

            // Delete inventory first (foreign key constraint)
            DB::table('inventory')->where('product_id', $id)->delete();
            
            // Delete product
            DB::table('products')->where('product_id', $id)->delete();

            DB::commit();

            return redirect()->route('admin.products')->with('success', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function orders()
    {
        $orders = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->leftJoin('ordersdetail', 'orders.order_id', '=', 'ordersdetail.order_id')
            ->leftJoin('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->select([
                'orders.*',
                DB::raw('COALESCE(orders.customer_name, customer.nama) as customer_name'),
                DB::raw('COALESCE(orders.customer_address, customer.alamat) as customer_email'),
                DB::raw('GROUP_CONCAT(CONCAT(products.product_name, " (", ordersdetail.quantity, ")") SEPARATOR ", ") as products_list')
            ])
            ->groupBy('orders.order_id', 'orders.customer_id', 'orders.order_date', 'orders.total_price', 'orders.status', 'orders.created_at', 'orders.updated_at', 'orders.customer_name', 'customer.nama', 'orders.customer_address', 'customer.alamat')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('admin.orders', compact('orders'));
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,sedang dibuat,dalam perjalanan,selesai,dibatalkan'
        ]);

        DB::table('orders')
            ->where('order_id', $id)
            ->update([
                'status' => $validated['status'],
                'updated_at' => now()
            ]);

        return redirect()->route('admin.orders')->with('success', 'Status pesanan berhasil diupdate!');
    }

    public function orderDetail(int $id)
    {
        $order = DB::table('orders')
            ->where('orders.order_id', $id)
            ->first();

        if (!$order) {
            return redirect()->route('admin.orders')->with('error', 'Pesanan tidak ditemukan');
        }

        $orderDetails = DB::table('ordersdetail')
            ->join('products', 'ordersdetail.product_id', '=', 'products.product_id')
            ->select(['ordersdetail.*', 'products.product_name', 'products.price', 'products.image_url'])
            ->where('ordersdetail.order_id', $id)
            ->get();

        return view('admin.order-detail', compact('order', 'orderDetails'));
    }

    public function verifyPayment(Request $request, int $id)
    {
        $order = DB::table('orders')->where('order_id', $id)->first();

        if (!$order) {
            return redirect()->route('admin.orders')->with('error', 'Pesanan tidak ditemukan');
        }

        // Cek apakah pembayaran menggunakan QRIS
        if ($order->payment_method !== 'qris') {
            return redirect()->route('admin.order.detail', $id)->with('error', 'Hanya pembayaran QRIS yang perlu diverifikasi');
        }

        // Cek apakah sudah ada bukti pembayaran
        if (!$order->payment_proof_path) {
            return redirect()->route('admin.order.detail', $id)->with('error', 'Bukti pembayaran belum ada');
        }

        // Cek apakah sudah diverifikasi sebelumnya
        if ($order->payment_verified_at) {
            return redirect()->route('admin.order.detail', $id)->with('info', 'Pembayaran sudah terverifikasi sebelumnya');
        }

        try {
            DB::beginTransaction();

            // Update order: set status menjadi "sedang dibuat" dan tandai pembayaran sebagai terverifikasi
            DB::table('orders')
                ->where('order_id', $id)
                ->update([
                    'status' => 'sedang dibuat',
                    'payment_verified_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('admin.order.detail', $id)->with('success', 'Pembayaran berhasil diverifikasi! Status pesanan diubah menjadi "Sedang Dibuat"');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.order.detail', $id)->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getPaymentProof(int $orderId)
    {
        $order = DB::table('orders')->where('order_id', $orderId)->first();

        if (!$order || !$order->payment_proof_path) {
            abort(404, 'File bukti pembayaran tidak ditemukan');
        }

        $filePath = $order->payment_proof_path;
        
        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File tidak ditemukan di storage');
        }

        return response()->file(Storage::disk('local')->path($filePath));
    }
}
