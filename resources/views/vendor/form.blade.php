@extends('layouts.main')
@php $x = $vendor ?? null; @endphp
@section('title', $x ? 'Ubah Vendor' : 'Tambah Vendor')
@section('content')
<form method="POST" action="{{ $x ? route('vendor.update', $x) : route('vendor.store') }}" class="card card-body" novalidate>
    @csrf @if($x) @method('PUT') @endif
    <div class="row g-3">
        <x-field name="nama" label="Nama Vendor" :value="$x->nama ?? ''" required maxlength="100" />
        <x-field name="email" type="email" label="Email" :value="$x->email ?? ''" maxlength="100" />
        <x-field name="telepon" label="Telepon" :value="$x->telepon ?? ''" maxlength="20" pattern="[0-9+\-\s]{8,20}" />
        <div class="col-md-6"></div>
        <x-textarea name="alamat" label="Alamat" :value="$x->alamat ?? ''" maxlength="300" />
    </div>
    <div class="mt-4"><button class="btn btn-primary">Simpan</button> <a href="{{ route('vendor.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection