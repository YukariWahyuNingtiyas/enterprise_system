@extends('layouts.main')
@section('title', 'Laporan ERP')
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3"><input type="date" name="dari" value="{{ $dari }}" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="sampai" value="{{ $sampai }}" class="form-control"></div>
    <div class="col"><button class="btn btn-primary">Tampilkan</button> <button type="button" onclick="window.print()" class="btn btn-outline-secondary">Cetak</button></div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card card-body">Pemasukan<h5 class="text-success">Rp {{ number_format($masuk,0,',','.') }}</h5></div></div>
    <div class="col-md-4"><div class="card card-body">Pengeluaran<h5 class="text-danger">Rp {{ number_format($keluar,0,',','.') }}</h5></div></div>
    <div class="col-md-4"><div class="card card-body">Saldo<h5>Rp {{ number_format($masuk-$keluar,0,',','.') }}</h5></div></div>
</div>

<h6>Ringkasan per Kategori</h6>
<table class="table table-sm table-bordered bg-white">
    <tr class="table-light"><th>Jenis</th><th>Kategori</th><th class="text-center">Jumlah</th><th class="text-end">Total</th></tr>
    @forelse($perKategori as $k)
        <tr><td>{{ ucfirst($k['jenis']) }}</td><td>{{ $k['kategori'] }}</td><td class="text-center">{{ $k['jumlah'] }}</td><td class="text-end">@rp($k['total'])</td></tr>
    @empty <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>@endforelse
</table>

<h6>Transaksi Keuangan</h6>
<table class="table table-sm table-bordered bg-white">
    <tr class="table-light"><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th class="text-end">Nominal</th></tr>
    @forelse($transaksi as $t)<tr><td>{{ $t->tanggal->format('d/m/Y') }}</td><td>{{ $t->jenis }}</td><td>{{ $t->kategori }}</td><td class="text-end">{{ number_format($t->nominal,0,',','.') }}</td></tr>
    @empty <tr><td colspan="4" class="text-muted text-center">Tidak ada data.</td></tr>@endforelse
</table>

<h6>Invoice</h6>
<table class="table table-sm table-bordered bg-white">
    <tr class="table-light"><th>Nomor</th><th>Pelanggan</th><th>Status</th><th class="text-end">Total</th></tr>
    @forelse($invoice as $i)<tr><td>{{ $i->nomor_invoice }}</td><td>{{ $i->customer->nama }}</td><td>{{ $i->status }}</td><td class="text-end">{{ number_format($i->total,0,',','.') }}</td></tr>
    @empty <tr><td colspan="4" class="text-muted text-center">Tidak ada data.</td></tr>@endforelse
</table>

<h6>Penggajian</h6>
<table class="table table-sm table-bordered bg-white">
    <tr class="table-light"><th>Periode</th><th>Karyawan</th><th class="text-end">Total Gaji</th></tr>
    @forelse($gaji as $g)<tr><td>{{ $g->periode }}</td><td>{{ $g->employee->nama }}</td><td class="text-end">{{ number_format($g->total_gaji,0,',','.') }}</td></tr>
    @empty <tr><td colspan="3" class="text-muted text-center">Tidak ada data.</td></tr>@endforelse
</table>
@endsection