<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class PageController extends Controller
{
    /**
     * Display the homepage
     */
    public function index()
    {
        // Jika user sudah login, arahkan ke menu
        if (Session::has('user_id')) {
            return redirect()->route('menu');
        }
        
        // Ambil semua produk dari database
        $products = DB::table('products')
            ->join('inventory', 'products.product_id', '=', 'inventory.product_id')
            ->select([
                'products.*', 
                'inventory.stock',
                DB::raw('(SELECT AVG(rating) FROM product_ratings WHERE product_id = products.product_id) as avg_rating'),
                DB::raw('(SELECT COUNT(*) FROM product_ratings WHERE product_id = products.product_id) as rating_count')
            ])
            ->get();

        return view('index', compact('products'));
    }

    /**
     * Display the login page
     */
    public function login()
    {
        // Jika user sudah login, arahkan ke menu
        if (Session::has('user_id')) {
            return redirect()->route('menu');
        }
        
        return view('login');
    }

    /**
     * Display the signup page
     */
    public function signup()
    {
        // Jika user sudah login, arahkan ke menu
        if (Session::has('user_id')) {
            return redirect()->route('menu');
        }
        
        return view('signup');
    }

    /**
     * Display the menu/shop page
     */
    public function menu()
    {
        // Ambil semua produk dari database dengan stok
        $products = DB::table('products')
            ->join('inventory', 'products.product_id', '=', 'inventory.product_id')
            ->select([
                'products.*', 
                'inventory.stock',
                DB::raw('(SELECT AVG(rating) FROM product_ratings WHERE product_id = products.product_id) as avg_rating'),
                DB::raw('(SELECT COUNT(*) FROM product_ratings WHERE product_id = products.product_id) as rating_count')
            ])
            ->get();

        return view('menu', compact('products'));
    }

    /**
     * Display the sage team page
     */
    public function sageteam()
    {
        return view('sageteam');
    }

    /**
     * Display the rafiffebrian page
     */
    public function rafiffebrian()
    {
        return view('rafiffebrian');
    }

    /**
     * Handle login form submission
     */
    public function handleLogin(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Cari user berdasarkan email
        $user = DB::table('users')->where('email', $request->input('email'))->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            // Login berhasil, simpan session
            Session::put('user_id', $user->id);
            Session::put('user_name', $user->name);
            Session::put('user_email', $user->email);
            
            // Ambil customer_id dari tabel customer berdasarkan user_id
            $customer = DB::table('customer')->where('user_id', $user->id)->first();
            if ($customer) {
                Session::put('customer_id', $customer->customer_id);
            }

            return redirect()->route('menu')->with('success', 'Selamat datang, ' . $user->name . '!');
        }

        // Login gagal
        return back()->with('error', 'Email atau password salah!')->withInput();
    }

    /**
     * Handle signup form submission
     */
    public function handleSignup(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm-password' => 'required|same:password',
        ]);

        try {
            // Insert user baru ke tabel users
            $userId = DB::table('users')->insertGetId([
                'name' => $validated['fullname'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Insert customer record
            DB::table('customer')->insert([
                'user_id' => $userId,
                'nama' => $validated['fullname'],
                'no_telp' => '',
                'alamat' => '',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan login.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        Session::forget('user_id');
        Session::forget('user_name');
        Session::forget('user_email');
        Session::forget('customer_id');
        Session::flush();

        return redirect()->route('index')->with('success', 'Berhasil logout!');
    }
}
