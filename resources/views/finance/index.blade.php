@extends('layouts.main')
@section('title', 'ERP · Finance')
@section('subtitle', 'Catatan pemasukan dan pengeluaran perusahaan')

@php
    $peran    = auth()->user()->role->nama ?? '';
    $bisaUbah = in_array($peran, ['admin', 'keuangan'], true);
    $rp       = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $saldo    = $totalMasuk - $totalKeluar;
@endphp

@section('actions')
    @if($bisaUbah)
        <a href="{{ route('finance.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Transaksi</a>
    @endif
@endsection

@section('content')

{{-- ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card">
        <div class="stat-icon g-green"><i class="bi bi-graph-up-arrow"></i></div>
        <div><small>Total Pemasukan</small><h5>{{ $rp($totalMasuk) }}</h5></div></div></div>
    <div class="col-md-4"><div class="card stat-card">
        <div class="stat-icon g-red"><i class="bi bi-graph-down-arrow"></i></div>
        <div><small>Total Pengeluaran</small><h5>{{ $rp($totalKeluar) }}</h5></div></div></div>
    <div class="col-md-4"><div class="card stat-card">
        <div class="stat-icon {{ $saldo >= 0 ? 'g-blue' : 'g-amber' }}"><i class="bi bi-wallet2"></i></div>
        <div><small>Saldo</small><h5 class="{{ $saldo < 0 ? 'text-danger' : '' }}">{{ $rp($saldo) }}</h5></div></div></div>
</div>

{{-- filter --}}
<div class="card mb-4"><div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3 col-lg-2"><label class="form-label">Dari tanggal</label>
            <input type="date" name="dari" value="{{ request('dari') }}" class="form-control"></div>
        <div class="col-md-3 col-lg-2"><label class="form-label">Sampai tanggal</label>
            <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control"></div>
        <div class="col-md-3 col-lg-2"><label class="form-label">Jenis</label>
            <select name="jenis" class="form-select">
                <option value="">Semua jenis</option>
                <option value="pemasukan" @selected(request('jenis') == 'pemasukan')>Pemasukan</option>
                <option value="pengeluaran" @selected(request('jenis') == 'pengeluaran')>Pengeluaran</option>
            </select></div>
        <div class="col-md-3 col-lg-3"><label class="form-label">Kategori</label>
            <input name="kategori" value="{{ request('kategori') }}" placeholder="Cari kategori" class="form-control"></div>
        <div class="col-lg-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->hasAny(['dari', 'sampai', 'jenis', 'kategori']))
                <a href="{{ route('finance.index') }}" class="btn btn-light">Atur ulang</a>
            @endif
        </div>
    </form>
</div></div>

{{-- tabel --}}
<div class="card">
    <div class="table-responsive">
        <table class="table align-middle table-hover">
            <thead><tr>
                <th>Kode</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th>
                <th class="text-end">Nominal</th><th>Deskripsi</th>@if($bisaUbah)<th class="text-end">Aksi</th>@endif
            </tr></thead>
            <tbody>
            @forelse($data as $d)
                <tr>
                    <td class="text-nowrap fw-semibold">{{ $d->kode_transaksi }}</td>
                    <td class="text-nowrap">{{ $d->tanggal->format('d/m/Y') }}</td>
                    <td><span class="badge rounded-pill text-bg-{{ $d->jenis == 'pemasukan' ? 'success' : 'danger' }}">{{ ucfirst($d->jenis) }}</span></td>
                    <td>{{ $d->kategori }}</td>
                    <td class="text-end text-nowrap fw-semibold {{ $d->jenis == 'pemasukan' ? 'text-success' : 'text-danger' }}">
                        {{ $d->jenis == 'pemasukan' ? '+' : '−' }} {{ $rp($d->nominal) }}
                    </td>
                    <td class="text-muted" style="min-width:200px">{{ $d->deskripsi ?: '-' }}</td>
                    @if($bisaUbah)
                    <td class="text-end text-nowrap">
                        @if(!$d->invoice_id)
                            <a href="{{ route('finance.edit', $d) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form action="{{ route('finance.destroy', $d) }}" method="POST" data-confirm="Hapus transaksi {{ $d->kode_transaksi }}? Jurnalnya ikut terhapus.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash3"></i>Hapus</button>
                            </form>
                        @else
                            <span class="chip chip-gray">dari invoice</span>
                        @endif
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $bisaUbah ? 7 : 6 }}"><div class="empty"><i class="bi bi-inbox"></i>Belum ada transaksi.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $data->links() }}</div>
@endsection