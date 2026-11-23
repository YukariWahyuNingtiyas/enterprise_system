@extends('layouts.main')
@section('title', 'ESCM · Inventory')
@section('subtitle', 'Daftar barang, stok, dan peringatan stok menipis')
@section('actions')
    @if(auth()->user()->hasRole('admin','manajer_gudang'))
        <a href="{{ route('movement.create') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-right"></i> Barang Masuk/Keluar</a>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Barang</a>
    @endif
@endsection
@section('content')
@php $tulis = auth()->user()->hasRole('admin','manajer_gudang'); @endphp

@if($menipis->isNotEmpty())
<div class="card border-start border-4 border-warning mb-3">
    <div class="card-header text-warning-emphasis"><i class="bi bi-exclamation-triangle-fill"></i> Peringatan Stok Menipis ({{ $menipis->count() }} barang)</div>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead><tr><th>Barang</th><th class="text-center">Stok</th><th class="text-center">Minimum</th><th class="text-center">Rekomendasi Pesan</th><th>Vendor</th><th></th></tr></thead>
        <tbody>
        @foreach($menipis as $m)
            <tr><td class="fw-semibold">{{ $m->nama }}</td><td class="text-center text-danger fw-bold">{{ $m->stok }}</td>
                <td class="text-center">{{ $m->stok_minimum }}</td><td class="text-center">{{ $m->rekomendasi_pesan }}</td>
                <td>{{ $m->vendor->nama ?? '-' }}</td>
                <td class="text-end">@if($tulis)<a class="btn btn-sm btn-warning" href="{{ route('po.create', ['inventory_id' => $m->id, 'jumlah' => $m->rekomendasi_pesan]) }}">Buat PO</a>@endif</td></tr>
        @endforeach
        </tbody></table></div>
</div>
@endif

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Cari kode / nama barang"></div>
    <div class="col-md-3 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="menipis" value="1" id="mn" @checked(request('menipis'))><label for="mn" class="form-check-label">Hanya stok menipis</label></div></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Kode</th><th>Nama</th><th class="text-center">Stok</th><th class="text-center">Min</th><th class="text-end">Harga Jual</th><th>Rak</th><th>Vendor</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $b)
        <tr><td>{{ $b->kode_barang }}</td><td class="fw-semibold">{{ $b->nama }}</td>
            <td class="text-center"><span class="badge text-bg-{{ $b->stok <= $b->stok_minimum ? 'danger' : 'success' }}">{{ $b->stok }}</span></td>
            <td class="text-center">{{ $b->stok_minimum }}</td><td class="text-end">@rp($b->harga_jual)</td>
            <td>{{ $b->lokasi_rak ?: '-' }}</td><td>{{ $b->vendor->nama ?? '-' }}</td>
            <td class="text-end">@if($tulis)
                <a href="{{ route('inventory.edit', $b) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('inventory.destroy', $b) }}" method="POST" class="d-inline" data-confirm="Hapus barang ini?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif</td></tr>
    @empty
        <tr><td colspan="8"><div class="empty"><i class="bi bi-box-seam"></i>Belum ada barang.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection