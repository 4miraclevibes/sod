<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        try {
            $shipping_price = 10000;
            $app_fee = 1000;
            $carts = Cart::with('variant.product')->where('user_id', Auth::user()->id)->get();
            
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Cart data retrieved successfully',
                'data' => [
                    'carts' => $carts,
                    'shipping_price' => $shipping_price,
                    'app_fee' => $app_fee
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while retrieving cart data',
                'error' => 'Error pada CartController: ' . $e->getMessage() . ' (File: ' . $e->getFile() . ', Baris: ' . $e->getLine() . ')'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (Auth::user()->role->name == 'driver') {
                return response()->json([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Driver tidak dapat menambahkan produk ke keranjang'
                ], 403);
            }

            $request->validate([
                'variant_id' => 'required',
                'quantity' => 'required|numeric|min:1',
            ]);

            $userId = Auth::user()->id;
            $variant = ProductVariant::findOrFail($request->variant_id);
            if ($request->quantity > $variant->getAvailableStockCount()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Jumlah yang dipilih melebihi stok yang tersedia'
                ], 400);
            }

            $existingCart = Cart::where('user_id', $userId)
                                ->where('variant_id', $request->variant_id)
                                ->first();

            if ($existingCart) {
                $existingCart->quantity += $request->quantity;
                $existingCart->save();
            } else {
                Cart::create([
                    'variant_id' => $request->variant_id,
                    'quantity' => $request->quantity,
                    'user_id' => $userId,
                ]);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Produk berhasil ditambahkan ke keranjang'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while adding product to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $items = $request->input('items', []);
            
            if (empty($items)) {
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Tidak ada item yang dipilih untuk dihapus'
                ], 400);
            }

            $deletedCount = Cart::whereIn('id', $items)->where('user_id', Auth::user()->id)->delete();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => $deletedCount . ' item telah dihapus dari keranjang'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while removing items from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
