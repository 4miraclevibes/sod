<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\VariantStock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'variants', 'images')->get();
        return view('pages.dashboard.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('pages.dashboard.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $product = Product::create($request->all());
        $product->variants()->create([
            'name' => 'default',
            'price' => $request->price,
            'is_visible' => true,
        ]);
        return redirect()->route('dashboard.product.index');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('pages.dashboard.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        return redirect()->route('dashboard.product.index');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('dashboard.product.index');
    }

    public function productImage(Product $product)
    {
        return view('pages.dashboard.products.images.index', compact('product'));
    }

    public function productImageStore(Product $product, Request $request)
    {
        $product->images()->create($request->all());
        return redirect()->route('dashboard.product.image.index', $product->id);
    }

    public function productImageDestroy(ProductImage $productImage)
    {
        $productImage->delete();
        return redirect()->route('dashboard.product.image.index', $productImage->product_id);
    }

    public function productVariant(Product $product)
    {
        return view('pages.dashboard.products.variants.index', compact('product'));
    }

    public function productVariantCreate(Product $product)
    {
        return view('pages.dashboard.products.variants.create', compact('product'));
    }

    public function productVariantStore(Product $product, Request $request)
    {
        $product->variants()->create([
            'name' => $request->name,
            'price' => $request->price,
            'is_visible' => 0,
        ]);
        return redirect()->route('dashboard.product.variant.index', $product->id);
    }

    public function productVariantEdit(ProductVariant $variant)
    {
        return view('pages.dashboard.products.variants.edit', compact('variant'));
    }

    public function productVariantUpdate(Request $request, ProductVariant $variant)
    {
        $variant->update($request->all());
        return redirect()->route('dashboard.product.variant.index', $variant->product_id);
    }

    public function productVariantDestroy(ProductVariant $variant)
    {
        $variant->delete();
        return redirect()->route('dashboard.product.variant.index', $variant->product_id);
    }

    public function productVariantStock(ProductVariant $variant)
    {
        return view('pages.dashboard.products.variants.stock.index', compact('variant'));
    }

    public function productVariantStockStore(ProductVariant $variant, Request $request)
    {
        $capitalPrice = $request->capital_price;
        $variantStocks = $variant->variantStocks()->create([
            'quantity' => $request->quantity,
        ]);
        foreach ($variantStocks->quantity as $quantity) {
            $variantStocks->stockDetails()->create([
                'variant_stock_id' => $variantStocks->id,
                'capital_price' => $capitalPrice,
                'price' => 0,
                'status' => 'ready',
            ]);
        }
        return redirect()->route('dashboard.product.variant.stock.index', $variant->id);
    }

    public function productVariantStockDestroy(VariantStock $variantStock)
    {
        $variantStock->delete();
        return redirect()->route('dashboard.product.variant.stock.index', $variantStock->variant_id);
    }

    public function productVariantStockDetail(VariantStock $variantStock)
    {
        return view('pages.dashboard.products.variants.stock.detail', compact('variantStock'));
    }
}