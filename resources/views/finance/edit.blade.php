@extends('layouts.main')
@section('title', 'Ubah Transaksi')
@section('content')
<form method="POST" action="{{ route('finance.update', $finance) }}" class="card card-body">
    @csrf @method('PUT')
    @include('finance._form')
    <div class="mt-3"><button class="btn btn-primary">Perbarui</button>
        <a href="{{ route('finance.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection