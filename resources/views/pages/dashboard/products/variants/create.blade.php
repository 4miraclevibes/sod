@extends('layouts.dashboard.main')

@section('content')
<div class="card container p-3">
    <h3 class="mb-3 text-center">Tambah Varian Produk Baru</h3>
    <a href="{{ route('dashboard.product.variant.index', $product->id) }}" class="btn btn-secondary btn-sm">Kembali</a>
    <form action="{{ route('dashboard.product.variant.store', $product->id) }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nama Varian</label>
                <input type="text" class="form-control form-control-sm" id="name" name="name" required>
            </div>
            <div class="col-md-6">
                <label for="price" class="form-label">Harga</label>
                <input type="number" class="form-control form-control-sm" id="price" name="price" required>
            </div>
            <div class="col-md-6">
                <label for="capital_price" class="form-label">Harga Modal</label>
                <input type="number" class="form-control form-control-sm" id="capital_price" name="capital_price">
            </div> 
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Tambah Varian</button>
        <a href="{{ route('dashboard.product.variant.index', $product->id) }}" class="btn btn-sm btn-secondary">Batal</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Tambahkan script tambahan jika diperlukan
</script>
@endpush