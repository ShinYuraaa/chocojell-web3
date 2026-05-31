<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FeedbackController extends Controller
{
    /**
     * Display feedback page
     */
    public function index()
    {
        return view('feedback');
    }

    /**
     * Store customer feedback
     */
    public function store(Request $request)
    {
        // Check if user is logged in
        if (!Session::has('customer_id')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk memberikan feedback.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000'
        ]);

        try {
            DB::table('customer_feedback')->insert([
                'customer_id' => Session::get('customer_id'),
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('feedback')->with('success', 'Terima kasih atas feedback Anda!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim feedback: ' . $e->getMessage());
        }
    }
}

