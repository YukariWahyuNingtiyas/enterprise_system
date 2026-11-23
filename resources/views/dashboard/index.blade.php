@extends('layouts.main')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan sistem sesuai peran Anda')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    // Kartu statistik (dibuat di sini supaya tidak perlu berkas tambahan)
    $kartu = fn ($warna, $ikon, $label, $nilai, $w = 3) =>
        '<div class="col-md-6 col-xl-' . $w . '"><div class="card stat-card">'
        . '<div class="stat-icon ' . $warna . '"><i class="bi ' . $ikon . '"></i></div>'
        . '<div><small>' . e($label) . '</small><h5>' . e($nilai) . '</h5></div></div></div>';

    $tgl  = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m') : '-';
    $link = fn ($nama, $model) => \Illuminate\Support\Facades\Route::has($nama) ? route($nama, $model) : '#';
@endphp

@if(!empty($peringatan))
<div class="alert alert-warning">
    <b><i class="bi bi-exclamation-triangle-fill"></i> Sebagian data dashboard belum bisa dimuat:</b>
    <ul class="mb-0 mt-2 small">
        @foreach($peringatan as $p)<li>{{ $p }}</li>@endforeach
    </ul>
    <div class="small mt-2">Salin teks di atas untuk memperbaikinya. Menu lain tetap bisa dipakai.</div>
</div>
@endif

{{-- ===================== ERP ===================== --}}
@if($erp)
<h6 class="text-uppercase text-muted small mb-2">ERP · Keuangan &amp; SDM</h6>
<div class="row g-3 mb-3">
    {!! $kartu('g-green',  'bi-graph-up-arrow',   'Total Pemasukan',   $rp($pemasukan)) !!}
    {!! $kartu('g-red',    'bi-graph-down-arrow', 'Total Pengeluaran', $rp($pengeluaran)) !!}
    {!! $kartu('g-blue',   'bi-receipt',          'Jumlah Invoice',    $jumlahInvoice) !!}
    {!! $kartu('g-amber',  'bi-people',           'Karyawan Aktif',    $jumlahKaryawan) !!}
    {!! $kartu('g-violet', 'bi-wallet2',          'Gaji Periode ' . ($periodeGaji ?: '-'), $rp($totalGaji), 6) !!}
    {!! $kartu('g-red',    'bi-shield-exclamation','Transaksi Terindikasi Anomali (AI)', $anomali . ' transaksi', 6) !!}
</div>
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card"><div class="card-header">Pemasukan vs Pengeluaran per Bulan</div>
            <div class="card-body"><canvas id="cFin" height="120"></canvas></div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-header">Transaksi Terbaru</div>
            <ul class="list-group list-group-flush">
                @forelse($transaksi as $t)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $tgl($t->tanggal) }} · {{ $t->kategori }}</span>
                    <span class="fw-semibold {{ $t->jenis == 'pemasukan' ? 'text-success' : 'text-danger' }}">
                        {{ $t->jenis == 'pemasukan' ? '+' : '-' }}{{ $rp($t->nominal) }}
                    </span>
                </li>
                @empty
                <li class="list-group-item text-muted">Belum ada transaksi.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endif

{{-- ===================== CRM ===================== --}}
@if($crm)
<h6 class="text-uppercase text-muted small mb-2">CRM · Penjualan</h6>
<div class="row g-3 mb-3">
    {!! $kartu('g-cyan',   'bi-person-lines-fill', 'Total Prospek', $totalLead) !!}
    {!! $kartu('g-green',  'bi-people-fill',       'Pelanggan',     $totalCustomer) !!}
    {!! $kartu('g-amber',  'bi-chat-dots',         'Negotiation',   $pipeline['negotiation'] ?? 0) !!}
    {!! $kartu('g-violet', 'bi-truck',             'Pesanan Dalam Pengiriman', $orderKirim->count()) !!}
</div>
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Pipeline Penjualan</div>
            <div class="card-body"><canvas id="cPipe" height="200"></canvas></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Prospek Prioritas (AI)</div>
            <ul class="list-group list-group-flush">
                @forelse($leadPrioritas as $l)
                <li class="list-group-item d-flex justify-content-between">
                    <a href="{{ $link('lead.show', $l) }}" class="text-decoration-none">{{ $l->nama }}</a>
                    <span class="badge text-bg-{{ $l->prioritas[1] ?? 'secondary' }}">
                        {{ number_format($l->skor_prioritas, 0) }} · {{ $l->prioritas[0] ?? '' }}
                    </span>
                </li>
                @empty
                <li class="list-group-item text-muted small">Belum dihitung. Buka menu Analitik AI.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Status Pengiriman Pesanan</div>
            <ul class="list-group list-group-flush">
                @forelse($orderKirim as $o)
                <li class="list-group-item d-flex justify-content-between">
                    <a href="{{ $link('order.show', $o) }}" class="text-decoration-none">{{ $o->customer->nama ?? '-' }}</a>
                    <span class="badge text-bg-secondary">{{ $o->status_pengiriman }}</span>
                </li>
                @empty
                <li class="list-group-item text-muted small">Tidak ada pesanan berjalan.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endif

{{-- ===================== ESCM ===================== --}}
@if($escm)
<h6 class="text-uppercase text-muted small mb-2">ESCM · Persediaan &amp; Pemasok</h6>
<div class="row g-3 mb-3">
    {!! $kartu('g-blue',  'bi-box-seam-fill',           'Jenis Barang', $totalBarang) !!}
    {!! $kartu('g-green', 'bi-boxes',                   'Total Stok',   number_format($totalStok, 0, ',', '.')) !!}
    {!! $kartu('g-red',   'bi-exclamation-triangle-fill','Stok Menipis', $menipis->count() . ' barang') !!}
    {!! $kartu('g-amber', 'bi-clipboard-check',         'PO Aktif · Pesanan Menunggu', $poAktif . ' · ' . $pesananMasuk) !!}
</div>
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card"><div class="card-header text-danger">Barang dengan Stok Menipis</div>
            <ul class="list-group list-group-flush">
                @forelse($menipis as $m)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $m->nama }}</span>
                    <span><span class="badge text-bg-danger">{{ $m->stok }}</span> / min {{ $m->stok_minimum }}</span>
                </li>
                @empty
                <li class="list-group-item text-muted small">Semua stok aman.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header">Purchase Order Terbaru</div>
            <ul class="list-group list-group-flush">
                @forelse($poTerbaru as $p)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $p->nomor_po }} · {{ $p->inventory->nama ?? '-' }} ({{ $p->jumlah }})</span>
                    <span class="badge text-bg-secondary">{{ $p->status }}</span>
                </li>
                @empty
                <li class="list-group-item text-muted small">Belum ada PO.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endif

@if(!$erp && !$crm && !$escm)
<div class="alert alert-info">Belum ada ringkasan untuk peran Anda.</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($erp)
if (document.getElementById('cFin')) {
    new Chart(document.getElementById('cFin'), {
        type: 'bar',
        data: {
            labels: @json($grafik->pluck('bulan')),
            datasets: [
                {label: 'Pemasukan',   data: @json($grafik->pluck('masuk')),  backgroundColor: '#10b981', borderRadius: 6},
                {label: 'Pengeluaran', data: @json($grafik->pluck('keluar')), backgroundColor: '#f43f5e', borderRadius: 6}
            ]
        }
    });
}
@endif
@if($crm)
if (document.getElementById('cPipe')) {
    new Chart(document.getElementById('cPipe'), {
        type: 'doughnut',
        data: {
            labels: ['Prospect', 'Negotiation', 'Deal', 'Lost'],
            datasets: [{
                data: [{{ $pipeline['prospect'] ?? 0 }}, {{ $pipeline['negotiation'] ?? 0 }}, {{ $pipeline['deal'] ?? 0 }}, {{ $pipeline['lost'] ?? 0 }}],
                backgroundColor: ['#06b6d4', '#f59e0b', '#10b981', '#f43f5e']
            }]
        }
    });
}
@endif
</script>
@endpush