@extends('layouts.landing.main')

@section('style')
<style>
    .quantity-control {
        display: flex;
        align-items: center;
        background-color: #e8f5e9;
        border-radius: 20px;
        padding: 2px;
    }
    .quantity-btn {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: #4caf50;
        padding: 0 8px;
        cursor: pointer;
    }
    .quantity-input {
        background: none;
        border: none;
        text-align: center;
        width: 30px;
        font-weight: bold;
    }
    .order-info {
        background-color: #4caf50;
        color: white;
        border-radius: 10px;
        padding: 15px;
    }
    .order-button {
        background-color: #4caf50;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 10px;
        width: 100%;
        font-weight: bold;
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<form action="{{ route('transaction.store') }}" method="POST" id="orderForm">
    @csrf
    <input type="hidden" name="total_price" id="hiddenTotal">
    <input type="hidden" name="shipping_price" id="hiddenShippingPrice">
    <input type="hidden" name="app_fee" id="hiddenAppFee"> <!-- Tambahkan ini -->
    <div class="container mt-3">
        <div class="d-flex justify-content-between mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll" checked>
                <label class="form-check-label" for="selectAll">Pilih Semua</label>
            </div>
            <button type="button" id="deleteButton" class="btn btn-link text-danger p-0">Hapus</button>
        </div>

        <div class="cart-items">
            @foreach($carts as $index => $cart)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input item-checkbox" type="checkbox" name="checked_items[]" value="{{ $cart->id }}" data-index="{{ $index }}" checked>
                        </div>
                        <img src="{{ $cart->variant->product->thumbnail }}" alt="{{ $cart->variant->product->name }}" class="img-fluid ms-2" style="width: 60px; height: 60px; object-fit: cover;">
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-0">{{ $cart->variant->product->name }}</h6>
                            <small class="text-muted">{{ $cart->variant->name }}</small>
                            <p class="mb-0 fw-bold">Rp {{ number_format($cart->variant->price, 0, ',', '.') }}</p>
                        </div>
                        <div class="quantity-control">
                            <button type="button" class="quantity-btn minus" data-index="{{ $index }}">-</button>
                            <input type="text" class="quantity-input" name="quantities[{{ $cart->id }}]" value="{{ $cart->quantity }}" readonly>
                            <button type="button" class="quantity-btn plus" data-index="{{ $index }}">+</button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="order-info mt-4 mb-3">
            <h6 class="mb-3">Order Info</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span id="subtotal">Rp0</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Ongkos Kirim</span>
                <span id="shippingCost">Rp0</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Biaya Layanan</span>
                <span id="appFee">Rp0</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span id="total">Rp0</span>
            </div>
        </div>

        <button type="submit" class="order-button mb-5" id="orderButton" disabled>
            Pesan Sekarang <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let items = @json($carts);
    let shippingCost = @json($shipping_price); // Anda bisa mengubah ini sesuai kebutuhan
    let appFee = @json($app_fee); // Anda bisa mengubah ini sesuai kebutuhan
    const orderButton = document.getElementById('orderButton');
    function updateQuantity(index, change) {
        if (items[index] && items[index].quantity !== undefined) {
            items[index].quantity = Math.max(1, items[index].quantity + change);
            let quantityInput = document.querySelectorAll('.quantity-input')[index];
            if (quantityInput) {
                quantityInput.value = items[index].quantity;
            }
            updateTotal();
        }
    }

    function updateTotal() {
        console.log('Items:', items);
        let subtotal = 0;
        let anyItemChecked = false;
        items.forEach((item, index) => {
            let checkbox = document.querySelectorAll('.item-checkbox')[index];
            if (checkbox && checkbox.checked) {
                anyItemChecked = true;
                if (item && item.variant && item.variant.price) {
                    subtotal += item.variant.price * item.quantity;
                } else {
                    console.error('Item tidak memiliki properti variant.price:', item);
                }
            }
        });
        let total = subtotal + shippingCost + appFee;
        console.log('Subtotal:', subtotal);
        console.log('Total:', total);

        let subtotalElement = document.getElementById('subtotal');
        let shippingCostElement = document.getElementById('shippingCost');
        let appFeeElement = document.getElementById('appFee');
        let totalElement = document.getElementById('total');
        let hiddenTotalElement = document.getElementById('hiddenTotal');
        let hiddenShippingPriceElement = document.getElementById('hiddenShippingPrice');
        let hiddenAppFeeElement = document.getElementById('hiddenAppFee');

        if (subtotalElement) subtotalElement.textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
        if (shippingCostElement) shippingCostElement.textContent = `Rp${shippingCost.toLocaleString('id-ID')}`;
        if (appFeeElement) appFeeElement.textContent = `Rp${appFee.toLocaleString('id-ID')}`;
        if (totalElement) totalElement.textContent = `Rp${total.toLocaleString('id-ID')}`;
        if (hiddenTotalElement) hiddenTotalElement.value = total;
        if (hiddenShippingPriceElement) hiddenShippingPriceElement.value = shippingCost;
        if (hiddenAppFeeElement) hiddenAppFeeElement.value = appFee; // Pastikan ini diisi

        // Aktifkan atau nonaktifkan tombol berdasarkan apakah ada item yang dipilih
        if (orderButton) {
            orderButton.disabled = !anyItemChecked;
        }
    }

    // Tangani klik tombol minus
    document.querySelectorAll('.minus').forEach((btn, index) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            updateQuantity(index, -1);
        });
    });

    // Tangani klik tombol plus
    document.querySelectorAll('.plus').forEach((btn, index) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            updateQuantity(index, 1);
        });
    });

    document.querySelectorAll('.item-checkbox').forEach((checkbox, index) => {
        checkbox.addEventListener('change', updateTotal);
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateTotal();
    });

    // Panggil updateTotal saat halaman dimuat
    updateTotal();

    // Tambahkan event listener untuk form submission
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        let checkedItems = document.querySelectorAll('.item-checkbox:checked');
        if (checkedItems.length === 0) {
            e.preventDefault();
            alert('Pilih setidaknya satu item untuk dipesan');
        }
    });

    // Tambahkan event listener untuk tombol hapus
    document.getElementById('deleteButton').addEventListener('click', function(e) {
        e.preventDefault();
        let checkedItems = document.querySelectorAll('.item-checkbox:checked');
        if (checkedItems.length === 0) {
            alert('Pilih setidaknya satu item untuk dihapus');
            return;
        }
        if (confirm('Apakah Anda yakin ingin menghapus item yang dipilih?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("cart.destroy") }}';
            form.innerHTML = `@csrf @method('DELETE')`;
            checkedItems.forEach(item => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'items[]';
                input.value = item.value;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>
@endsection