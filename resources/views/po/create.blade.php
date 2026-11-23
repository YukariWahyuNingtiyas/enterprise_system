@extends('layouts.main')
@section('title', 'Buat Purchase Order')
@section('content')
<form method="POST" action="{{ route('po.store') }}" class="card card-body" novalidate>
    @csrf
    <div class="row g-3">
        <x-select name="inventory_id" label="Barang" :options="$barang" :value="$inventory_id" placeholder="-- pilih barang --" required />
        <x-select name="vendor_id" label="Vendor" :options="$vendors" :value="$vendor_id" placeholder="-- pilih vendor --" required />
        <x-field name="jumlah" type="number" label="Jumlah Pesanan" :value="$jumlah" min="1" required />
        <x-field name="tanggal" type="date" label="Tanggal Pemesanan" :value="date('Y-m-d')" required />
    </div>
    <div class="mt-4"><button class="btn btn-primary">Simpan sebagai Draft</button> <a href="{{ route('po.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection