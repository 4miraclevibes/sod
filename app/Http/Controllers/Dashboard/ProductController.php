<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\VariantStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Service\ServiceController;

class ProductController extends Controller
{
    protected $serviceController;

    public function __construct(ServiceController $serviceController)
    {
        $this->serviceController = $serviceController;
    }

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
        $packingPrice = 500;
        $appFee = 0.2;
        $profit = 0.3;
        $total = $appFee + $profit;
        $adjustment = $total * $request->price;
        $price = $request->price + $adjustment + $packingPrice;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->serviceController->uploadImage($request->file('thumbnail'));
            
            $product = Product::create([
                ...$request->except('thumbnail'),
                'thumbnail' => $thumbnailUrl
            ]);
        } else {
            $product = Product::create($request->except('thumbnail'));
        }

        // Buat variant default
        $product->variants()->create([
            'name' => 'default',
            'price' => $price,
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
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->serviceController->uploadImage($request->file('thumbnail'));
            
            $product->update([
                ...$request->except('thumbnail'),
                'thumbnail' => $thumbnailUrl
            ]);
        } else {
            $product->update($request->except('thumbnail'));
        }

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
        $appFee = 0.2;
        $profit = 0.3;
        $total = $appFee + $profit;
        $packingPrice = 500;
        $adjustment = $total * $request->price;
        $price = $request->price + $adjustment + $packingPrice;
        $product->variants()->create([
            'name' => $request->name,
            'price' => $price,
            'is_visible' => 0,
        ]);
        return redirect()->route('dashboard.product.variant.index', $product->id);
    }

    public function productVariantEdit(ProductVariant $variant)
    {
        $appFee = 0.2;
        $profit = 0.3;
        $packingPrice = 500;
        $total = $appFee + $profit;
        $adjustment = $total * $variant->price;
        $price = $variant->price - $adjustment - $packingPrice;
        dd(
            $variant->price,
            $total,
            $adjustment,
            $packingPrice,
            $price,
        );
        return view('pages.dashboard.products.variants.edit', compact('variant', 'price'));
    }

    public function productVariantUpdate(Request $request, ProductVariant $variant)
    {
        $appFee = 0.2;
        $profit = 0.3;
        $packingPrice = 500;
        $total = $appFee + $profit;
        $adjustment = $total * $request->price;
        $price = $request->price + $adjustment + $packingPrice;
        $variant->update([
            ...$request->except('price'),
            'price' => $price,
        ]);
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
            'capital_price' => $capitalPrice,
        ]);

        for ($i = 0; $i < $variantStocks->quantity; $i++) {
            $variantStocks->stockDetails()->create([
                'variant_stock_id' => $variantStocks->id,
                'capital_price' => $capitalPrice,
                'price' => 0,
                'status' => 'ready',
            ]);
        }

        return redirect()->route('dashboard.product.variant.stock.index', $variant->id)->with('success', 'Stok berhasil ditambahkan');
    }

    public function productVariantStockDestroy(VariantStock $variantStock)
    {
        $variantStock->delete();
        return back()->with('success', 'Stok berhasil dihapus');
    }

    public function productVariantStockDetail(VariantStock $variantStock)
    {
        return view('pages.dashboard.products.variants.stock.detail', compact('variantStock'));
    }
}