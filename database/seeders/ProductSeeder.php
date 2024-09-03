<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'category_id' => 2,
                'name' => 'Selada Keriting Organik',
                'slug' => 'selada-keriting-organik',
                'stock' => 10,
                'thumbnail' => 'https://ik.imagekit.io/dcjlghyytp1/ec47f3a852e1fe5c7df8b5748585758f?tr=f-auto,w-1000',
                'description' => 'Selada Keriting Organik',
            ],
            [
                'category_id' => 2,
                'name' => 'Selada Romaine',
                'slug' => 'selada-romaine',
                'stock' => 20,
                'thumbnail' => 'https://ik.imagekit.io/dcjlghyytp1/f76c43e835a198d319b6ebbcc56a29ac?tr=f-auto,w-1000',
                'description' => 'Product 2 description',
            ],
            [
                'category_id' => 2,
                'name' => 'Selada Air',
                'slug' => 'selada-air',
                'stock' => 30,
                'thumbnail' => 'https://ik.imagekit.io/dcjlghyytp1/c1473db9ba6f9b4ad637f1a814c5c4c8?tr=f-auto,w-360',
                'description' => 'Product 3 description',
            ],
            [
                'category_id' => 2,
                'name' => 'Selada Romaine Hidroponik',
                'slug' => 'selada-romaine-hidroponik',
                'stock' => 40,
                'thumbnail' => 'https://ik.imagekit.io/dcjlghyytp1/98954be200236bbcf6812b45e56b5d89?tr=f-auto,w-1000',
                'description' => 'Product 4 description',
            ],
            [
                'category_id' => 1,
                'name' => 'Cabai Merah Besar',
                'slug' => 'cabai-merah-besar',
                'stock' => 50,
                'thumbnail' => 'https://ik.imagekit.io/dcjlghyytp1/4b322210d94741fc807ef9f4d8fc665d?tr=f-auto,w-1000',
                'description' => 'Product 5 description',
            ],
            [
                'category_id' => 1,
                'name' => 'Cabai Hijau Besar',
                'slug' => 'cabai-hijau-besar',
                'stock' => 60,
                'thumbnail' => 'https://ik.imagekit.io/dcjlghyytp1/9d779017d2ba8de8dfb2d730be0503c2?tr=f-auto,w-1000',
                'description' => 'Product 6 description',
            ],
        ];

        foreach ($products as $product) {
            $createdProduct = Product::create($product);

            $this->createProductVariants($createdProduct->id);
            $this->createProductImages($createdProduct->id);
        }
    }

    private function createProductVariants($productId)
    {
        $variants = [
            [
                ['name' => '10 gram', 'price' => 10000, 'is_visible' => 1, 'capital_price' => 5000],
                ['name' => '20 gram', 'price' => 20000, 'is_visible' => 1, 'capital_price' => 10000],
            ],
            [
                ['name' => '100 Gram', 'price' => 30000, 'is_visible' => 1, 'capital_price' => 15000],
                ['name' => '200 Gram', 'price' => 40000, 'is_visible' => 1, 'capital_price' => 20000],
            ],
            [
                ['name' => '100 Gram', 'price' => 50000, 'is_visible' => 1, 'capital_price' => 25000],
                ['name' => '200 Gram', 'price' => 60000, 'is_visible' => 1, 'capital_price' => 30000],
            ],
            [
                ['name' => '1 kg', 'price' => 70000, 'is_visible' => 1, 'capital_price' => 35000],
                ['name' => '1.5 kg', 'price' => 80000, 'is_visible' => 1, 'capital_price' => 40000],
            ],
            [
                ['name' => '1 kg', 'price' => 90000, 'is_visible' => 1, 'capital_price' => 45000],
                ['name' => '1.5 kg', 'price' => 100000, 'is_visible' => 1, 'capital_price' => 50000],
            ],
            [
                ['name' => '2 kg', 'price' => 110000, 'is_visible' => 1, 'capital_price' => 55000],
                ['name' => '2 kg', 'price' => 120000, 'is_visible' => 1, 'capital_price' => 60000],
            ],
        ][$productId - 1];

        foreach ($variants as $variant) {
            ProductVariant::create(array_merge(['product_id' => $productId], $variant));
        }
    }

    private function createProductImages($productId)
    {
        $images = [
            [
                'https://ik.imagekit.io/dcjlghyytp1/ec47f3a852e1fe5c7df8b5748585758f?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/8ed1bc771fc39cbc3bd1576abbdaa010?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/9d639f90fac68deef9dbddec3453fa9c?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/ec47f3a852e1fe5c7df8b5748585758f?tr=f-auto,w-1000',
            ],
            [
                'https://ik.imagekit.io/dcjlghyytp1/f76c43e835a198d319b6ebbcc56a29ac?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/42dfd8edf5dc718584eb362320cf5698?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/076ba3582fa2629f1245cf34809c5678?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/dd66f8072e94672c84c46c733e4d4d29?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/bea1d92be1a5d1fec90f713632cc9f42?tr=f-auto,w-1000',
            ],
            [
                'https://ik.imagekit.io/dcjlghyytp1/76cecf714d9af4a9d86ebccfa725dcd9?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/c1473db9ba6f9b4ad637f1a814c5c4c8?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/2b5bf169894bce3451f753d55179a395?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/a7a03650c1004af0e7a4864d6766aa2e?tr=f-auto,w-1000',
            ],
            [
                'https://ik.imagekit.io/dcjlghyytp1/98954be200236bbcf6812b45e56b5d89?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/2c84966db1645920e198b6c090f84778?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/712f0597a78ca0d41ff813400a306bf8?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/98954be200236bbcf6812b45e56b5d89?tr=f-auto,w-1000',
            ],
            [
                'https://ik.imagekit.io/dcjlghyytp1/4b322210d94741fc807ef9f4d8fc665d?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/dfe5cd0e3cefb511581fd97423980828?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/75c1c470d36ed62a47edca55db306fd4?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/c99b075e15c8d586fead78b9ec76dffb?tr=f-auto,w-1000',
            ],
            [
                'https://ik.imagekit.io/dcjlghyytp1/9d779017d2ba8de8dfb2d730be0503c2?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/9f4f7b11aaeb558ff02c1865610169e3?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/b6f5913699b40826975f724fa74d945e?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/b5dbf919332b4479783d70d5df358654?tr=f-auto,w-1000',
                'https://ik.imagekit.io/dcjlghyytp1/79c49a1fa3439b8409dadc76f2210c10?tr=f-auto,w-1000',
            ],
        ][$productId - 1];

        foreach ($images as $image) {
            ProductImage::create([
                'product_id' => $productId,
                'image' => $image,
            ]);
        }
    }
}
