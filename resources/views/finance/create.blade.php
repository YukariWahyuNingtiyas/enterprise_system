@extends('layouts.main')
@section('title', 'Tambah Transaksi')
@section('content')
<form method="POST" action="{{ route('finance.store') }}" class="card card-body">
    @csrf
    @include('finance._form')
    <div class="mt-3"><button class="btn btn-primary">Simpan</button>
        <a href="{{ route('finance.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection