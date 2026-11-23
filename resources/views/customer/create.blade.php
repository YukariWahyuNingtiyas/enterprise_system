@extends('layouts.main')
@section('title', 'Tambah Pelanggan')
@section('content')
<form method="POST" action="{{ route('customer.store') }}" class="card card-body" novalidate>
    @csrf @include('customer._form')
    <div class="mt-4"><button class="btn btn-primary">Simpan</button> <a href="{{ route('customer.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection