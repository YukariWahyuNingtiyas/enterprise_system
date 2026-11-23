@extends('layouts.main')
@section('title', 'ESCM · Barang Masuk / Keluar')
@section('subtitle', 'Riwayat perubahan persediaan')
@section('actions')
    @if(auth()->user()->hasRole('admin','manajer_gudang'))<a href="{{ route('movement.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Catat Mutasi</a>@endif
@endsection
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><select name="inventory_id" class="form-select"><option value="">Semua barang</option>
        @foreach($barang as $id => $nama)<option value="{{ $id }}" @selected(request('inventory_id')==$id)>{{ $nama }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="tipe" class="form-select"><option value="">Masuk & keluar</option>
        <option value="masuk" @selected(request('tipe')=='masuk')>Masuk</option><option value="keluar" @selected(request('tipe')=='keluar')>Keluar</option></select></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th class="text-end">Jumlah</th><th>Referensi</th><th>Keterangan</th><th>Petugas</th></tr></thead>
    <tbody>
    @forelse($data as $m)
        <tr><td>{{ $m->tanggal->format('d/m/Y') }}</td><td>{{ $m->inventory->nama }}</td><td><x-badge :s="$m->tipe" /></td>
            <td class="text-end fw-semibold">{{ $m->tipe=='masuk' ? '+' : '-' }}{{ $m->jumlah }}</td>
            <td>{{ $m->referensi }}</td><td class="text-muted">{{ $m->keterangan }}</td><td>{{ $m->user->name ?? 'sistem' }}</td></tr>
    @empty
        <tr><td colspan="7"><div class="empty"><i class="bi bi-arrow-left-right"></i>Belum ada riwayat.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection