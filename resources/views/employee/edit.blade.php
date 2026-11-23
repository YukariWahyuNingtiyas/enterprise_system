@extends('layouts.main')
@section('title', 'Ubah Karyawan')
@section('content')
<form method="POST" action="{{ route('employee.update', $employee) }}" class="card card-body">
    @csrf @method('PUT') @include('employee._form')
    <div class="mt-3"><button class="btn btn-primary">Perbarui</button> <a href="{{ route('employee.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection