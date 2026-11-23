@extends('layouts.main')
@section('title', 'Ubah Lead')
@section('content')
<form method="POST" action="{{ route('lead.update', $lead) }}" class="card card-body" novalidate>
    @csrf @method('PUT') @include('lead._form')
    <div class="mt-4"><button class="btn btn-primary">Perbarui</button> <a href="{{ route('lead.show', $lead) }}" class="btn btn-light">Batal</a></div>
</form>
@endsection