<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\FeedbackController;

// Homepage
Route::get('/', [PageController::class, 'index'])->name('index');

// Auth Routes
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::post('/login', [PageController::class, 'handleLogin'])->name('login.submit');
Route::get('/signup', [PageController::class, 'signup'])->name('signup');
Route::post('/signup', [PageController::class, 'handleSignup'])->name('signup.submit');
Route::post('/logout', [PageController::class, 'logout'])->name('logout');
Route::get('/logout', [PageController::class, 'logout'])->name('logout.get');

// Other Pages
Route::get('/menu', [PageController::class, 'menu'])->name('menu');
Route::get('/sageteam', [PageController::class, 'sageteam'])->name('sageteam');
Route::get('/rafiffebrian', [PageController::class, 'rafiffebrian'])->name('rafiffebrian');

// Order & Checkout Routes
Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [OrderController::class, 'processOrder'])->name('checkout.process');
Route::get('/payment/{orderId}', [OrderController::class, 'payment'])->name('payment');
Route::post('/payment/{orderId}', [OrderController::class, 'confirmPayment'])->name('payment.confirm');
Route::get('/order-status/{orderId}', [OrderController::class, 'orderStatus'])->name('order.status');
Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('my.orders');

// Rating Routes
Route::post('/api/ratings', [RatingController::class, 'storeRating'])->name('rating.store');
Route::get('/api/products/{productId}/ratings', [RatingController::class, 'getProductRatings'])->name('product.ratings');

// Feedback Routes
Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

// Admin Login Routes (Tanpa Middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.submit');
});

// Admin Routes (Dengan Middleware - Hanya Admin yang bisa akses)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // Logout
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::get('/logout', [AdminController::class, 'logout'])->name('logout.get');
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Products Management
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('product.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('product.store');
    Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('product.edit');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('product.update');
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('product.delete');
    
    // Orders Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AdminController::class, 'orderDetail'])->name('order.detail');
    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('order.updateStatus');
    Route::post('/orders/{id}/verify-payment', [AdminController::class, 'verifyPayment'])->name('order.verifyPayment');
    Route::get('/payment-proof/{orderId}', [AdminController::class, 'getPaymentProof'])->name('payment.proof');
});
