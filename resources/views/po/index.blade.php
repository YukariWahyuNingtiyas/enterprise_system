@extends('layouts.main')
@section('title', 'ESCM · Purchase Order')
@section('subtitle', 'Pemesanan barang ke vendor; stok bertambah saat barang diterima')
@section('actions')
    @if(auth()->user()->hasRole('admin','manajer_gudang'))<a href="{{ route('po.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat PO</a>@endif
@endsection
@section('content')
@php
$tulis = auth()->user()->hasRole('admin','manajer_gudang');
$alur = ['draft'=>['dipesan'=>'Pesan ke Vendor','dibatalkan'=>'Batalkan'],'dipesan'=>['diterima'=>'Terima Barang','dibatalkan'=>'Batalkan']];
@endphp
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Semua status</option>
        @foreach(['draft','dipesan','diterima','dibatalkan'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nomor PO</th><th>Tanggal</th><th>Vendor</th><th>Barang</th><th class="text-center">Jumlah</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $p)
        <tr><td class="fw-semibold">{{ $p->nomor_po }}</td><td>{{ $p->tanggal->format('d/m/Y') }}</td>
            <td>{{ $p->vendor->nama }}</td><td>{{ $p->inventory->nama }}</td>
            <td class="text-center">{{ $p->jumlah }}</td><td><x-badge :s="$p->status" /></td>
            <td class="text-end">
            @if($tulis)
                @foreach($alur[$p->status] ?? [] as $kode => $label)
                    <form method="POST" action="{{ route('po.status', $p) }}" class="d-inline"
                          data-confirm="{{ $kode=='diterima' ? 'Stok barang akan bertambah '.$p->jumlah.' unit. Lanjutkan?' : ($kode=='dibatalkan' ? 'Batalkan PO ini?' : 'Kirim pesanan ke vendor?') }}">
                        @csrf @method('PATCH')<input type="hidden" name="status" value="{{ $kode }}">
                        <button class="btn btn-sm btn-outline-{{ $kode=='dibatalkan' ? 'danger' : 'primary' }}">{{ $label }}</button></form>
                @endforeach
                @if($p->status=='draft')
                    <form method="POST" action="{{ route('po.destroy', $p) }}" class="d-inline" data-confirm="Hapus PO draft ini?">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary"><i class="bi bi-trash"></i></button></form>
                @endif
            @endif
            </td></tr>
    @empty
        <tr><td colspan="7"><div class="empty"><i class="bi bi-clipboard-check"></i>Belum ada Purchase Order.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection