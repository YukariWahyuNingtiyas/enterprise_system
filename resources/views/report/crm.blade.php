@extends('layouts.main')
@section('title', 'Laporan CRM')
@section('subtitle', 'Pipeline, komunikasi, prospek prioritas, dan pengiriman')
@section('actions')<button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Cetak</button>@endsection
@section('content')
<div class="row g-3">
  <div class="col-lg-6"><div class="card"><div class="card-header">Pipeline Penjualan</div><table class="table mb-0">
    <thead><tr><th>Status</th><th class="text-center">Jumlah</th><th class="text-end">Total Anggaran</th></tr></thead>
    @foreach($pipeline as $p)<tr><td><x-badge :s="$p->status" /></td><td class="text-center">{{ $p->n }}</td><td class="text-end">@rp($p->total)</td></tr>@endforeach
  </table></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header">Komunikasi per Jenis</div><table class="table mb-0">
    @foreach($komunikasi as $k)<tr><td>{{ ucfirst(str_replace('_',' ',$k->channel)) }}</td><td class="text-end">{{ $k->n }}x</td></tr>@endforeach
  </table></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header">5 Prospek Prioritas Tertinggi (AI)</div><table class="table mb-0">
    @forelse($topLead as $l)<tr><td>{{ $l->nama }}</td><td class="text-end"><span class="badge text-bg-{{ $l->prioritas[1] }}">{{ number_format($l->skor_prioritas,1) }} · {{ $l->prioritas[0] }}</span></td></tr>
    @empty<tr><td class="text-muted">Skor belum dihitung.</td></tr>@endforelse
  </table></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header">Status Pengiriman Pesanan</div><table class="table mb-0">
    @forelse($pengiriman as $p)<tr><td><x-badge :s="$p->status_pengiriman" /></td><td class="text-end">{{ $p->n }} pesanan</td></tr>
    @empty<tr><td class="text-muted">Belum ada pesanan terkonfirmasi.</td></tr>@endforelse
  </table></div></div>
</div>
@endsection