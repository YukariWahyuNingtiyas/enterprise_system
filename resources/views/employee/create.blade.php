@extends('layouts.main')
@section('title', 'Tambah Karyawan')
@section('content')
<form method="POST" action="{{ route('employee.store') }}" class="card card-body">
    @csrf @include('employee._form')
    <div class="mt-3"><button class="btn btn-primary">Simpan</button> <a href="{{ route('employee.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection