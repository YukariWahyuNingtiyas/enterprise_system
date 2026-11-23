@extends('layouts.main')
@section('title', auth()->user()->hasRole('manajer_gudang') ? 'ESCM · Pesanan Masuk' : 'CRM · Pesanan')
@section('subtitle', 'Status pesanan dan pengiriman (terhubung CRM, ERP, dan ESCM)')
@section('actions')
    @if(auth()->user()->hasRole('admin','sales'))<a href="{{ route('order.create') }}" class="btn btn-primary"><i class="bi bi-bag-plus"></i> Buat Pesanan</a>@endif
@endsection
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Cari nomor / pelanggan"></div>
    <div class="col-md-3"><select name="pengiriman" class="form-select"><option value="">Semua pengiriman</option>
        @foreach(['menunggu','diproses','dikirim','diterima'] as $s)<option value="{{ $s }}" @selected(request('pengiriman')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nomor</th><th>Tanggal</th><th>Pelanggan</th><th class="text-end">Total</th><th>Pesanan</th><th>Pengiriman</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $o)
        <tr><td class="fw-semibold">{{ $o->nomor_order }}</td><td>{{ $o->tanggal->format('d/m/Y') }}</td><td>{{ $o->customer->nama }}</td>
            <td class="text-end">@rp($o->total)</td><td><x-badge :s="$o->status" /></td>
            <td>@if($o->status==='dikonfirmasi')<x-badge :s="$o->status_pengiriman" />@else <span class="text-muted">-</span>@endif</td>
            <td class="text-end"><a href="{{ route('order.show', $o) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
    @empty
        <tr><td colspan="7"><div class="empty"><i class="bi bi-bag"></i>Belum ada pesanan.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection