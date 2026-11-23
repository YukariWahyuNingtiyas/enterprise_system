@extends('layouts.main')
@section('title', 'Tambah Lead')
@section('content')
<form method="POST" action="{{ route('lead.store') }}" class="card card-body" novalidate>
    @csrf @include('lead._form')
    <div class="mt-4"><button class="btn btn-primary">Simpan</button> <a href="{{ route('lead.index') }}" class="btn btn-light">Batal</a></div>
</form>
@endsection