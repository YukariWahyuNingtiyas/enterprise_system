@extends('layouts.main')
@section('title', 'CRM · Pelanggan')
@section('subtitle', 'Daftar pelanggan dan jumlah pesanan')
@section('actions')
    @if(auth()->user()->hasRole('admin','sales'))<a href="{{ route('customer.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pelanggan</a>@endif
@endsection
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-5"><input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Cari nama / email"></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama</th><th>Email</th><th>Telepon</th><th class="text-center">Pesanan</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $c)
        <tr><td><a class="fw-semibold text-decoration-none" href="{{ route('customer.show', $c) }}">{{ $c->nama }}</a></td>
            <td>{{ $c->email ?: '-' }}</td><td>{{ $c->telepon ?: '-' }}</td>
            <td class="text-center"><span class="badge text-bg-light border">{{ $c->orders_count }}</span></td>
            <td class="text-end">
                <a href="{{ route('customer.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                @if(auth()->user()->hasRole('admin','sales'))
                <a href="{{ route('customer.edit', $c) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('customer.destroy', $c) }}" method="POST" class="d-inline" data-confirm="Hapus pelanggan ini?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endif
            </td></tr>
    @empty
        <tr><td colspan="5"><div class="empty"><i class="bi bi-people"></i>Belum ada pelanggan.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection