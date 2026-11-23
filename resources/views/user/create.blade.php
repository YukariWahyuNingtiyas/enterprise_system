@extends('layouts.main')
@section('title', 'Tambah Pengguna')
@section('content')
<form method="POST" action="{{ route('user.store') }}" class="card card-body" novalidate>
    @csrf @include('user._form')
    <div class="mt-4"><button class="btn btn-primary">Simpan</button> <a href="{{ route('user.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection