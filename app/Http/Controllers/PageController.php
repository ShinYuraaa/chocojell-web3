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
        // Ambil produk dari database dengan stok
        $products = DB::table('products')
            ->join('inventory', 'products.product_id', '=', 'inventory.product_id')
            ->select(['products.*', 'inventory.stock'])
            ->where('inventory.stock', '>', 0) // Hanya produk yang ada stoknya
            ->limit(4) // Batasi 4 produk untuk homepage
            ->get();

        return view('index', compact('products'));
    }

    /**
     * Display the login page
     */
    public function login()
    {
        return view('login');
    }

    /**
     * Display the signup page
     */
    public function signup()
    {
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
            ->select(['products.*', 'inventory.stock'])
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
            DB::table('users')->insert([
                'name' => $validated['fullname'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
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
        Session::flush();

        return redirect()->route('index')->with('success', 'Berhasil logout!');
    }
}
