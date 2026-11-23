@extends('layouts.main')
@section('title', 'Catat Barang Masuk / Keluar')
@section('subtitle', 'Stok bertambah atau berkurang otomatis sesuai jenis mutasi')
@section('content')
<form method="POST" action="{{ route('movement.store') }}" class="card card-body" novalidate>
    @csrf
    <div class="row g-3">
        <x-select name="inventory_id" label="Barang" :options="$barang" placeholder="-- pilih barang --" required col="col-md-6" />
        <x-select name="tipe" label="Jenis Mutasi" :options="['masuk'=>'Barang Masuk','keluar'=>'Barang Keluar']" required col="col-md-3" />
        <x-field name="jumlah" type="number" label="Jumlah" min="1" :value="1" required col="col-md-3" />
        <x-field name="tanggal" type="date" label="Tanggal" :value="date('Y-m-d')" max="{{ date('Y-m-d') }}" required col="col-md-4" />
        <x-field name="keterangan" label="Keterangan" maxlength="255" col="col-md-8" />
    </div>
    <div class="mt-4"><button class="btn btn-primary">Simpan</button> <a href="{{ route('movement.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection