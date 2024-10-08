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
        $categorySlug = $request->query('category');
        $categories = Category::all();
        $banners = Banner::where('is_active', true)->get();

        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $products = Product::where('category_id', $category->id)->get();
            $activeCategory = $category;
        } else {
            $products = Product::all();
            $activeCategory = null;
        }

        return view('pages.landing.home', compact('products', 'categories', 'activeCategory', 'banners'));
    }

    public function productDetail($slug)
    {
        $product = Product::where('slug', $slug)->with('variants')->firstOrFail();
        return view('pages.landing.productDetail', compact('product'));
    }
}