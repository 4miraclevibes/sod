<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Asset;
class LandingController extends Controller
{
    public function home(Request $request)
    {
        $categories = Category::all();
        $banners = Banner::where('is_active', true)->get();
        $assets = Asset::where('is_active', true)->get();
        $products = Product::whereHas('variants', function($query) {
                $query->where('is_visible', true);
            })
            ->with(['variants' => function($query) {
                $query->where('is_visible', true);
            }])
            ->get();

        return view('pages.landing.home', compact('products', 'categories', 'banners', 'assets'));
    }

    public function productDetail($slug)
    {
        $product = Product::where('slug', $slug)->with('variants')->firstOrFail();
        return view('pages.landing.productDetail', compact('product'));
    }

    public function userDetail()
    {
        $assets = Asset::where('is_active', true)->get();
        return view('pages.landing.userDetails', compact('assets'));
    }
}
