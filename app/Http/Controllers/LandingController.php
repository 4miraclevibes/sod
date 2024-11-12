<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;

class LandingController extends Controller
{
    public function home(Request $request)
    {
        $categories = Category::all();
        $banners = Banner::where('is_active', true)->get();
        $products = Product::whereHas('variants', function($query) {
                $query->where('is_visible', true);
            })
            ->with(['variants' => function($query) {
                $query->where('is_visible', true);
            }])
            ->get();

        return view('pages.landing.home', compact('products', 'categories', 'banners'));
    }

    public function productDetail($slug)
    {
        $product = Product::where('slug', $slug)->with('variants')->firstOrFail();
        return view('pages.landing.productDetail', compact('product'));
    }
}