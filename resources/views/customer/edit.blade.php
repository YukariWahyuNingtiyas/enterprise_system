@extends('layouts.main')
@section('title', 'Ubah Pelanggan')
@section('content')
<form method="POST" action="{{ route('customer.update', $customer) }}" class="card card-body" novalidate>
    @csrf @method('PUT') @include('customer._form')
    <div class="mt-4"><button class="btn btn-primary">Perbarui</button> <a href="{{ route('customer.show', $customer) }}" class="btn btn-light">Batal</a></div>
</form>
@endsection