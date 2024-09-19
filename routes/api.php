<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LandingController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/home', [LandingController::class, 'home']);
Route::get('/product-detail/{slug}', [LandingController::class, 'productDetail']);

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart', [CartController::class, 'destroy']);
    Route::get('/user', [UserController::class, 'userDetail']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/logout', [UserController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::put('/{transaction}/status', [TransactionController::class, 'updateStatus']);
        Route::post('/{transaction}/mark-as-done', [TransactionController::class, 'markAsDone']);
        Route::post('/{transaction}/pay', [TransactionController::class, 'pay']);
    });
});

