<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RatingController extends Controller
{
    /**
     * Get product ratings and reviews
     */
    public function getProductRatings(int $productId)
    {
        $ratings = DB::table('product_ratings')
            ->join('customer', 'product_ratings.customer_id', '=', 'customer.customer_id')
            ->select([
                'product_ratings.*',
                'customer.nama as customer_name'
            ])
            ->where('product_ratings.product_id', $productId)
            ->orderBy('product_ratings.created_at', 'desc')
            ->get();

        $avgRating = DB::table('product_ratings')
            ->where('product_id', $productId)
            ->avg('rating');

        return response()->json([
            'average_rating' => round($avgRating ?? 0, 1),
            'total_ratings' => count($ratings),
            'ratings' => $ratings
        ]);
    }

    /**
     * Store product rating
     */
    public function storeRating(Request $request)
    {
        // Check if user is logged in
        if (!Session::has('customer_id')) {
            return response()->json([
                'error' => 'Silakan login terlebih dahulu untuk memberikan rating',
                'message' => 'Silakan login terlebih dahulu untuk memberikan rating'
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000'
        ]);

        // Check if user already rated this product
        $existingRating = DB::table('product_ratings')
            ->where('product_id', $request->input('product_id'))
            ->where('customer_id', Session::get('customer_id'))
            ->first();

        if ($existingRating) {
            // Update existing rating
            DB::table('product_ratings')
                ->where('rating_id', $existingRating->rating_id)
                ->update([
                    'rating' => $request->input('rating'),
                    'review_text' => $request->input('review_text'),
                    'updated_at' => now()
                ]);

            return response()->json(['message' => 'Rating berhasil diperbarui!']);
        } else {
            // Create new rating
            DB::table('product_ratings')->insert([
                'customer_id' => Session::get('customer_id'),
                'product_id' => $request->input('product_id'),
                'rating' => $request->input('rating'),
                'review_text' => $request->input('review_text'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Rating berhasil disimpan!']);
        }
    }
}

