<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\TransactionController as DashboardTransactionController;
use App\Http\Controllers\Dashboard\PaymentController as DashboardPaymentController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SSOController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'home'])->name('home');
Route::get('/product/{slug}', [LandingController::class, 'productDetail'])->name('product.detail');

Route::get('/cart', [CartController::class, 'index'])->middleware('auth')->name('cart');
Route::post('/cart/store', [CartController::class, 'store'])->middleware('auth')->name('cart.store');
Route::delete('/cart/destroy', [CartController::class, 'destroy'])->middleware('auth')->name('cart.destroy');
Route::get('/transaction', [TransactionController::class, 'index'])->middleware('auth')->name('transaction');
Route::post('/transaction/store', [TransactionController::class, 'store'])->middleware('auth')->name('transaction.store');
Route::patch('/transaction/{transaction}/mark-as-done', [TransactionController::class, 'markAsDone'])->middleware('auth')->name('transaction.markAsDone');
Route::patch('/transaction/{transaction}/pay', [TransactionController::class, 'pay'])->middleware('auth')->name('transaction.pay');

Route::get('/checkout', function () {
    return view('pages.landing.checkout');
})->name('checkout');

Route::get('/login/sso', function () {
    return view('pages.dashboard.auth.login');
})->middleware('notLogin')->name('login.sso');

Route::get('/user/details', function () {
    return view('pages.landing.userDetails');
})->middleware('auth')->name('user.details');

// Cart Success
Route::get('/cart/success', function () {
    return view('pages.landing.cartSuccess');
})->name('cart.success');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dashboard
// Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.', 'middleware' => 'auth'], function () {
    // User
    Route::get('user/index', [UserController::class, 'index'])->name('user.index');
    Route::get('user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('user/store', [UserController::class, 'store'])->name('user.store');
    Route::get('user/edit/{user}', [UserController::class, 'edit'])->name('user.edit');
    Route::put('user/update/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('user/destroy/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    // Category
    Route::get('category/index', [CategoryController::class, 'index'])->name('category.index');
    Route::get('category/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('category/store', [CategoryController::class, 'store'])->name('category.store');
    Route::get('category/edit/{category}', [CategoryController::class, 'edit'])->name('category.edit');
    Route::put('category/update/{category}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('category/destroy/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');
    // Product
    Route::get('product/index', [ProductController::class, 'index'])->name('product.index');
    Route::get('product/create', [ProductController::class, 'create'])->name('product.create');
    Route::post('product/store', [ProductController::class, 'store'])->name('product.store');
    Route::get('product/edit/{product}', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('product/update/{product}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('product/destroy/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
    // Product Variant
    Route::get('product/variant/index/{product}', [ProductController::class, 'productVariant'])->name('product.variant.index');
    Route::get('product/variant/create/{product}', [ProductController::class, 'productVariantCreate'])->name('product.variant.create');
    Route::post('product/variant/store/{product}', [ProductController::class, 'productVariantStore'])->name('product.variant.store');
    Route::get('product/variant/edit/{variant}', [ProductController::class, 'productVariantEdit'])->name('product.variant.edit');
    Route::put('product/variant/update/{variant}', [ProductController::class, 'productVariantUpdate'])->name('product.variant.update');
    Route::delete('product/variant/destroy/{variant}', [ProductController::class, 'productVariantDestroy'])->name('product.variant.destroy');
    // Product Image
    Route::get('product/image/index/{product}', [ProductController::class, 'productImage'])->name('product.image.index');
    Route::post('product/image/store/{product}', [ProductController::class, 'productImageStore'])->name('product.image.store');
    Route::delete('product/image/destroy/{productImage}', [ProductController::class, 'productImageDestroy'])->name('product.image.destroy');
    // Transaction
    Route::get('transaction/index', [DashboardTransactionController::class, 'index'])->name('transaction.index');
    Route::patch('transaction/updateStatus/{transaction}', [DashboardTransactionController::class, 'updateStatus'])->name('transaction.updateStatus');
    Route::get('transaction/show/{transaction}', [DashboardTransactionController::class, 'show'])->name('transaction.show');
    // Deliveries
    Route::get('transaction/deliveries', [DashboardTransactionController::class, 'deliveries'])->name('transaction.deliveries');
    // Payment
    Route::post('payment/store/{transaction}', [DashboardPaymentController::class, 'store'])->name('payment.store');
});

// Route untuk melakukan login dengan SSO
Route::post('/login/sso', [SSOController::class, 'login'])->name('login.sso');
Route::get('/dashboard', [SSOController::class, 'dashboard'])->middleware('login')->name('dashboard');
// Route untuk mendapatkan data pengguna dari SSO
Route::get('/user', [SSOController::class, 'getUser'])->name('user');
// Route untuk melakukan logout dengan SSO
Route::post('/logout/sso', [SSOController::class, 'logout'])->name('logout.sso');

require __DIR__.'/auth.php';