@extends('layouts.main')
@section('title', 'Laporan ESCM')
@section('subtitle', 'Posisi stok, mutasi bulanan, dan status Purchase Order')
@section('actions')<button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Cetak</button>@endsection
@section('content')
<div class="card mb-3"><div class="card-header">Posisi Stok</div><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Kode</th><th>Barang</th><th class="text-center">Stok</th><th class="text-center">Minimum</th><th>Status</th><th>Vendor</th></tr></thead>
    @foreach($stok as $b)<tr><td>{{ $b->kode_barang }}</td><td>{{ $b->nama }}</td><td class="text-center">{{ $b->stok }}</td><td class="text-center">{{ $b->stok_minimum }}</td>
        <td>@if($b->stok <= $b->stok_minimum)<span class="badge text-bg-danger">Menipis</span>@else<span class="badge text-bg-success">Aman</span>@endif</td><td>{{ $b->vendor->nama ?? '-' }}</td></tr>@endforeach
</table></div></div>
<div class="row g-3">
  <div class="col-lg-7"><div class="card"><div class="card-header">Mutasi Stok per Bulan</div><table class="table table-sm mb-0">
    <thead><tr><th>Bulan</th><th>Tipe</th><th class="text-end">Total Unit</th></tr></thead>
    @foreach($mutasi as $m)<tr><td>{{ $m->bulan }}</td><td><x-badge :s="$m->tipe" /></td><td class="text-end">{{ $m->total }}</td></tr>@endforeach
  </table></div></div>
  <div class="col-lg-5"><div class="card"><div class="card-header">Purchase Order per Status</div><table class="table table-sm mb-0">
    @foreach($po as $p)<tr><td><x-badge :s="$p->status" /></td><td class="text-end">{{ $p->n }}</td></tr>@endforeach
  </table></div></div>
</div>
@endsection