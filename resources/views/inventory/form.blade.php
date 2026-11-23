@extends('layouts.main')
@php $x = $inventory ?? null; @endphp
@section('title', $x ? 'Ubah Barang' : 'Tambah Barang')
@section('content')
<form method="POST" action="{{ $x ? route('inventory.update', $x) : route('inventory.store') }}" class="card card-body" novalidate>
    @csrf @if($x) @method('PUT') @endif
    <div class="row g-3">
        <x-field name="kode_barang" label="Kode Barang" :value="$x->kode_barang ?? ''" required maxlength="20" pattern="[A-Za-z0-9\-]+" help="Huruf, angka, dan tanda minus." col="col-md-4" />
        <x-field name="nama" label="Nama Barang" :value="$x->nama ?? ''" required maxlength="100" col="col-md-8" />
        @if($x)
            <x-field name="stok_info" label="Stok Saat Ini" :value="$x->stok" disabled col="col-md-4"
                     help="Ubah stok lewat menu Barang Masuk/Keluar agar tercatat." />
        @else
            <x-field name="stok" type="number" label="Stok Awal" :value="0" min="0" required col="col-md-4" />
        @endif
        <x-field name="stok_minimum" type="number" label="Batas Minimal Stok" :value="$x->stok_minimum ?? 10" min="0" required col="col-md-4" />
        <x-field name="harga_jual" type="number" label="Harga Jual (Rp)" :value="$x->harga_jual ?? 0" min="0" step="100" required col="col-md-4" />
        <x-field name="lokasi_rak" label="Lokasi Rak" :value="$x->lokasi_rak ?? ''" maxlength="20" col="col-md-4" />
        <x-select name="vendor_id" label="Vendor Utama" :options="$vendors" :value="$x->vendor_id ?? ''" placeholder="-- belum ditentukan --" col="col-md-8" />
    </div>
    <div class="mt-4"><button class="btn btn-primary">Simpan</button> <a href="{{ route('inventory.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection