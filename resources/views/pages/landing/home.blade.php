@extends('layouts.landing.main')

@section('style')
<style>
    .categories-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .categories-wrapper::-webkit-scrollbar {
        display: none;
    }

    .category-item {
        flex: 0 0 auto;
        width: 60px;
        text-align: center;
    }

    .category-item img {
        background-color: white;
        border-radius: 10%;
        padding: 5px;
    }

    .category-item p {
        font-size: 0.7rem;
        margin-top: 5px;
    }

    .icon-wrapper {
        background-color: white;
        border-radius: 10%;
        padding: 5px;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-wrapper svg {
        width: 100%;
        height: 100%;
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card-img-top {
        height: 120px;
        object-fit: cover;
    }

    .product-thumbnail {
        width: 100%;
        height: 200px;
        object-fit: cover;
        object-position: center;
    }

    .btn-add {
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        padding: 0;
    }

    .wrapper {
        max-width: 480px;
        margin: 0 auto;
        background-color: white;
        padding-bottom: 60px;
    }

    footer {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 480px;
        background-color: white;
        z-index: 1000;
    }

    .modal.fade .modal-dialog {
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
    }

    .modal-dialog {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        margin: 0;
        max-width: 100%;
    }

    .modal-content {
        border-radius: 1rem 1rem 0 0;
        max-width: 480px; /* Sesuaikan dengan lebar maksimum konten utama */
        margin: 0 auto;
    }

    .variant-button {
        border: 1px solid #ddd;
        background-color: white;
        color: black;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }

    .variant-button.active {
        background-color: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f0f0f0;
        border-radius: 0.5rem;
        padding: 0.5rem 0.5rem;
        width: 110px;
    }

    .quantity-control button {
        background-color: transparent;
        border: none;
        font-size: 1.2rem;
        color: #4CAF50;
    }

    .quantity-control input {
        background-color: transparent;
        border: none;
        text-align: center;
        width: 3rem;
        font-size: 1rem;
    }

    .add-to-cart-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-size: 1rem;
        width: 100%;
    }

    .install-button {
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        color: #333;
        border: none;
        border-radius: 16px;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1),
                    0 1px 3px rgba(0,0,0,0.08);
        z-index: 999;
        display: none;
        gap: 10px;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .install-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .install-button i.bi-google-play {
        color: #4CAF50;
        font-size: 18px;
    }

    .install-button i.bi-apple {
        color: #333;
        font-size: 20px;
    }

    .install-button::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 16px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(76,175,80,0.2), rgba(0,0,0,0.05));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, 
                     linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, 
              linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
    }
</style>
@endsection

@section('content')
<div class="container">
    <section class="banner mb-3">
        <img src="{{ $banners->first()->image }}" alt="Banner" class="w-100 rounded">
    </section>

    <section class="categories mb-4">
        <h6 class="mb-3">Kategori</h6>
        <div class="categories-wrapper">
            <div class="d-flex">
                <div class="category-item me-3">
                    <a href="{{ route('home') }}" class="text-decoration-none {{ !$activeCategory ? 'fw-bold' : '' }}">
                        <div class="icon-wrapper card">
                            <img src="{{ asset('assets/landing/images/LogoSod.png') }}" alt="Semua Kategori" class="w-100">
                        </div>
                        <p class="mb-0 text-dark">Semua</p>
                    </a>
                </div>
                @foreach($categories as $category)
                <div class="category-item me-3">
                    <a href="{{ route('home', ['category' => $category->slug]) }}" class="text-decoration-none {{ $activeCategory && $activeCategory->id == $category->id ? 'fw-bold' : '' }}">
                        <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-100 card">
                        <p class="mb-0 text-dark">{{ $category->name }}</p>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="products">
        <h6 class="mb-3">Produk {{ $activeCategory ? $activeCategory->name : 'Semua Kategori' }}</h6>
        <div class="row g-3">
            @foreach($products as $product)
            <div class="col-6">
                <div class="card h-100">
                    <a href="{{ route('product.detail', $product->slug) }}">
                        <img src="{{ $product->thumbnail }}" class="card-img-top product-thumbnail" alt="{{ $product->name }}">
                    </a>
                    <div class="card-body p-2 d-flex flex-column">
                        <p class="card-title mb-0 fw-bold" style="font-size: 0.9rem">{{ $product->name }}</p>
                        <p class="card-text text-muted small mb-2" style="font-size: 0.8rem">{{ $product->description }}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Rp {{ number_format($product->variants->first()->price ?? 0, 0, ',', '.') }}</span>
                                <a href="#" class="btn-add text-decoration-none no-loading @auth {{ Auth::user()->role->name == 'user' ? '' : 'disabled' }} @endauth" 
                                   data-product-id="{{ $product->id }}"
                                   data-product-name="{{ $product->name }}"
                                   data-product-price="{{ number_format($product->variants->first()->price ?? 0, 0, ',', '.') }}"
                                   data-variants="{{ json_encode($product->variants->map(function($variant) {
                                       return [
                                           'id' => $variant->id,
                                           'name' => $variant->name,
                                           'price' => $variant->price,
                                           'stock' => $variant->getAvailableStockCount()
                                       ];
                                   })) }}">+</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade container" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <div class="d-flex mb-3">
                        <img id="productImage" src="" alt="Product Image" class="me-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 id="productName" class="mb-1"></h5>
                            <p id="productVariants" class="text-muted small mb-1"></p>
                            <p id="productPrice" class="fw-bold mb-0"></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="mb-2">Pilih Varian</p>
                        <div id="variantButtons" class="d-flex flex-wrap gap-2">
                            <!-- Varian buttons akan ditambahkan secara dinamis menggunakan JavaScript -->
                        </div>
                    </div>
                    
                    <p class="mb-2">Stok: <span id="productStock"></span></p>
                    
                    <div class="mb-3">
                        <p class="mb-2">Pilih Jumlah</p>
                        <div class="quantity-control">
                            <button id="decreaseQuantity">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" readonly>
                            <button id="increaseQuantity">+</button>
                        </div>
                    </div>
                    
                    <form id="addToCartForm" action="{{ route('cart.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="variant_id" id="variantId">
                        <input type="hidden" name="quantity" id="formQuantity">
                        <button type="submit" class="add-to-cart-btn">Tambah Ke Keranjang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<button id="installButton" class="install-button">
    <i class="bi bi-google-play"></i>
    <i class="bi bi-apple"></i>
    Install App
</button>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartButtons = document.querySelectorAll('.btn-add');
        const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
        const quantityInput = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decreaseQuantity');
        const increaseBtn = document.getElementById('increaseQuantity');
        let currentVariants = [];
        let selectedVariant = null;

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productImage = this.closest('.card').querySelector('.product-thumbnail').src;
                currentVariants = JSON.parse(this.dataset.variants);

                // Filter varian dengan stok > 0
                currentVariants = currentVariants.filter(variant => variant.stock > 0);

                document.getElementById('productName').textContent = productName;
                document.getElementById('productImage').src = productImage;
                document.getElementById('productVariants').textContent = currentVariants.map(v => v.name).join(', ');

                // Reset quantity
                quantityInput.value = 1;

                // Tambahkan tombol varian
                const variantButtons = document.getElementById('variantButtons');
                variantButtons.innerHTML = '';
                currentVariants.forEach(variant => {
                    const button = document.createElement('button');
                    button.textContent = variant.name;
                    button.classList.add('variant-button');
                    button.onclick = (e) => {
                        e.preventDefault();
                        selectVariant(variant);
                    };
                    variantButtons.appendChild(button);
                });

                // Pilih varian pertama secara default (jika ada)
                if (currentVariants.length > 0) {
                    selectVariant(currentVariants[0]);
                    modal.show();
                } else {
                    alert('Maaf, produk ini sedang tidak tersedia.');
                }
            });
        });

        function selectVariant(variant) {
            selectedVariant = variant;
            document.getElementById('variantId').value = variant.id;
            document.getElementById('productPrice').textContent = `Rp ${variant.price.toLocaleString('id-ID')}`;
            document.getElementById('productStock').textContent = variant.stock;
            document.querySelectorAll('.variant-button').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent === variant.name) {
                    btn.classList.add('active');
                }
            });
            updateQuantityControls();
        }

        function updateQuantityControls() {
            const stock = selectedVariant ? selectedVariant.stock : 0;
            const currentQuantity = parseInt(quantityInput.value);
            decreaseBtn.disabled = currentQuantity <= 1;
            increaseBtn.disabled = currentQuantity >= stock;
        }

        decreaseBtn.addEventListener('click', () => {
            if (quantityInput.value > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
                updateQuantityControls();
            }
        });

        increaseBtn.addEventListener('click', () => {
            const stock = selectedVariant ? selectedVariant.stock : 0;
            if (parseInt(quantityInput.value) < stock) {
                quantityInput.value = parseInt(quantityInput.value) + 1;
                updateQuantityControls();
            }
        });

        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const quantity = parseInt(quantityInput.value);
            const stock = selectedVariant ? selectedVariant.stock : 0;
            if (quantity > stock) {
                alert('Jumlah yang dipilih melebihi stok yang tersedia.');
            } else {
                document.getElementById('formQuantity').value = quantity;
                this.submit();
            }
        });
    });

    let deferredPrompt;

    // Fungsi untuk menampilkan tombol install
    function showInstallButton() {
        const installButton = document.getElementById('installButton');
        const ua = window.navigator.userAgent;
        const iOS = !!ua.match(/iPad/i) || !!ua.match(/iPhone/i);
        
        if (iOS) {
            // Untuk perangkat iOS
            installButton.addEventListener('click', () => {
                alert('Untuk menginstal aplikasi:\n1. Ketuk tombol Share/Bagikan\n2. Gulir ke bawah dan ketuk "Tambahkan ke Layar Utama"');
            });
        } else if (deferredPrompt) {
            // Untuk Android/Chrome yang mendukung PWA
            installButton.addEventListener('click', async () => {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
            });
        }
        
        // Selalu tampilkan tombol
        installButton.style.display = 'flex';
    }

    // Panggil fungsi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', showInstallButton);

    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showInstallButton();
    });
</script>
@endsection