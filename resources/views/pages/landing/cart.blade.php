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
    <input type="hidden" name="app_fee" id="hiddenAppFee">
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
                            <small class="text-muted">{{ $cart->variant->name }}</small> <br>
                            <small class="text-muted">Stok: {{ $cart->variant->getAvailableStockCount() }}</small>
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

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let items = {!! json_encode($carts->map(function($cart) {
        return [
            'id' => $cart->id,
            'quantity' => $cart->quantity,
            'variant' => [
                'id' => $cart->variant->id,
                'price' => $cart->variant->price,
                'stock' => $cart->variant->getAvailableStockCount()
            ]
        ];
    })) !!};
    let shippingCost = {!! json_encode($shipping_price) !!};
    let appFee = {!! json_encode($app_fee) !!};
    const orderButton = document.getElementById('orderButton');

    console.log('Items:', items);
    console.log('Shipping Cost:', shippingCost);
    console.log('App Fee:', appFee);

    // Fungsi untuk memperbarui kuantitas
    function updateQuantity(index, change) {
        if (items[index] && items[index].quantity !== undefined) {
            let newQuantity = Math.max(1, items[index].quantity + change);
            let availableStock = items[index].variant.stock;
            
            if (newQuantity <= availableStock) {
                items[index].quantity = newQuantity;
                let quantityInput = document.querySelectorAll('.quantity-input')[index];
                if (quantityInput) {
                    quantityInput.value = items[index].quantity;
                }
                updateTotal();
            } else {
                alert('Jumlah melebihi stok yang tersedia');
            }
        }
    }

    // Fungsi untuk memperbarui total
    function updateTotal() {
        let subtotal = 0;
        let anyItemChecked = false;
        items.forEach((item, index) => {
            let checkbox = document.querySelectorAll('.item-checkbox')[index];
            if (checkbox && checkbox.checked) {
                anyItemChecked = true;
                subtotal += item.variant.price * item.quantity;
            }
        });
        let total = subtotal + shippingCost + appFee;

        document.getElementById('subtotal').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
        document.getElementById('shippingCost').textContent = `Rp${shippingCost.toLocaleString('id-ID')}`;
        document.getElementById('appFee').textContent = `Rp${appFee.toLocaleString('id-ID')}`;
        document.getElementById('total').textContent = `Rp${total.toLocaleString('id-ID')}`;
        document.getElementById('hiddenTotal').value = total;
        document.getElementById('hiddenShippingPrice').value = shippingCost;
        document.getElementById('hiddenAppFee').value = appFee;

        orderButton.disabled = !anyItemChecked;
    }

    // Event listeners
    document.querySelectorAll('.minus').forEach((btn, index) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            updateQuantity(index, -1);
        });
    });

    document.querySelectorAll('.plus').forEach((btn, index) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            updateQuantity(index, 1);
        });
    });

    document.querySelectorAll('.item-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', updateTotal);
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateTotal();
    });

    // Inisialisasi
    updateTotal();

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

@endsection