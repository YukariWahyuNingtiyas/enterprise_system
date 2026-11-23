@extends('layouts.main')
@section('title', 'ESCM · Vendors')
@section('subtitle', 'Data pemasok barang')
@section('actions')
    @if(auth()->user()->hasRole('admin','manajer_gudang'))<a href="{{ route('vendor.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Vendor</a>@endif
@endsection
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-5"><input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Cari nama vendor"></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama</th><th>Alamat</th><th>Kontak</th><th class="text-center">PO</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $v)
        <tr><td class="fw-semibold">{{ $v->nama }}</td><td>{{ $v->alamat ?: '-' }}</td>
            <td>{{ $v->email }}<div class="small text-muted">{{ $v->telepon }}</div></td>
            <td class="text-center"><span class="badge text-bg-light border">{{ $v->purchase_orders_count }}</span></td>
            <td class="text-end">
            @if(auth()->user()->hasRole('admin','manajer_gudang'))
                <a href="{{ route('vendor.edit', $v) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('vendor.destroy', $v) }}" method="POST" class="d-inline" data-confirm="Hapus vendor ini?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            @endif</td></tr>
    @empty
        <tr><td colspan="5"><div class="empty"><i class="bi bi-truck"></i>Belum ada vendor.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection