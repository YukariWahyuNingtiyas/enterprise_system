@extends('layouts.main')
@section('title', 'ERP - HR')
@section('content')
@php $bisaUbah = auth()->user()->hasRole('admin','keuangan'); @endphp
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="cari" value="{{ request('cari') }}" placeholder="Cari nama / kode" class="form-control"></div>
    <div class="col"><button class="btn btn-secondary">Cari</button>
        @if($bisaUbah)<a href="{{ route('employee.create') }}" class="btn btn-primary">+ Karyawan</a>@endif</div>
</form>
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Email</th><th>Jabatan</th><th>Bagian</th><th>Bergabung</th><th>Status</th>@if($bisaUbah)<th>Aksi</th>@endif</tr></thead>
    @foreach($data as $e)
    <tr><td>{{ $e->kode_karyawan }}</td><td>{{ $e->nama }}</td><td>{{ $e->email }}</td><td>{{ $e->jabatan }}</td>
        <td>{{ $e->bagian }}</td><td>{{ $e->tanggal_bergabung->format('d/m/Y') }}</td>
        <td><span class="badge bg-{{ $e->status=='aktif'?'success':'secondary' }}">{{ $e->status }}</span></td>
        @if($bisaUbah)<td>
            <a href="{{ route('employee.edit', $e) }}" class="btn btn-sm btn-warning">Edit</a>
            <form action="{{ route('employee.destroy', $e) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus / nonaktifkan karyawan ini?')">
                @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Hapus</button></form></td>@endif
    </tr>
    @endforeach
</table>
{{ $data->links() }}
@endsection