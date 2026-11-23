@extends('layouts.main')
@section('title', 'Ubah Pengguna')
@section('content')
<form method="POST" action="{{ route('user.update', $user) }}" class="card card-body" novalidate>
    @csrf @method('PUT') @include('user._form')
    <div class="mt-4"><button class="btn btn-primary">Perbarui</button> <a href="{{ route('user.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection