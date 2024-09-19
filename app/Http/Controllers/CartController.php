<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $shipping_price = 10000;
        $app_fee = 1000;
        $carts = Cart::with('variant.product')->where('user_id', Auth::user()->id)->get();
        if ($carts->isEmpty()) {
            return view('pages.landing.cartEmpty');
        }
        if (Auth::user()->role->name == 'driver') {
            return view('pages.landing.home');
        }
        return view('pages.landing.cart', compact('carts', 'shipping_price', 'app_fee'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role->name == 'driver') {
            return back()->with('error', 'Driver tidak dapat menambahkan produk ke keranjang');
        }
        $request->validate([
            'variant_id' => 'required',
            'quantity' => 'required|numeric|min:1',
        ]);

        $userId = Auth::user()->id;

        //Apabila quantity melebihi stok yang tersedia
        $variant = ProductVariant::find($request->variant_id);
        if ($request->quantity > $variant->getAvailableStockCount()) {
            return back()->with('error', 'Jumlah yang dipilih melebihi stok yang tersedia');
        }

        // Cek apakah item dengan variant_id yang sama sudah ada di keranjang
        $existingCart = Cart::where('user_id', $userId)
                            ->where('variant_id', $request->variant_id)
                            ->first();

        if ($existingCart) {
            // Jika sudah ada, tambahkan kuantitasnya
            $existingCart->quantity += $request->quantity;
            $existingCart->save();
        } else {
            // Jika belum ada, buat item baru
            Cart::create([
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
                'user_id' => $userId,
            ]);
        }

        return redirect()->route('home')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function destroy(Request $request)
    {
        $items = $request->input('items', []);
        
        if (empty($items)) {
            return back()->with('error', 'Tidak ada item yang dipilih untuk dihapus');
        }

        Cart::whereIn('id', $items)->where('user_id', Auth::user()->id)->delete();

        return back()->with('success', 'Item yang dipilih telah dihapus dari keranjang');
    }
}