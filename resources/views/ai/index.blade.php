@extends('layouts.main')
@section('title', 'Analitik AI')
@section('subtitle', 'Rekomendasi dan peringatan berbasis algoritma. Hasilnya pendukung keputusan dan tetap perlu diperiksa pengguna.')

@section('actions')
@if(in_array($peran, ['admin', 'pimpinan'], true))
    <form method="POST" action="{{ route('ai.run', 'semua') }}" data-confirm="Jalankan semua analisis AI sekarang?">
        @csrf
        <button class="btn btn-primary"><i class="bi bi-lightning-charge-fill"></i> Jalankan Semua</button>
    </form>
@endif
@endsection

@section('content')
@php
    $rp   = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $tgl  = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m/Y') : '-';
    $link = fn ($nama, $id) => \Illuminate\Support\Facades\Route::has($nama) ? route($nama, $id) : '#';

    $kartu = fn ($warna, $ikon, $label, $nilai, $info = '') =>
        '<div class="col-6 col-xl-3"><div class="card stat-card">'
        . '<div class="stat-icon ' . $warna . '"><i class="bi ' . $ikon . '"></i></div>'
        . '<div><small>' . e($label) . '</small><h5>' . e($nilai) . '</h5>'
        . ($info ? '<small class="text-muted">' . e($info) . '</small>' : '') . '</div></div></div>';

    $aktif = request('tab', $tab[0] ?? 'lead');
    if (!in_array($aktif, $tab, true)) { $aktif = $tab[0] ?? 'lead'; }

    $judul = [
        'lead'     => ['Lead Scoring', 'bi-bullseye'],
        'anomali'  => ['Deteksi Anomali Keuangan', 'bi-shield-exclamation'],
        'forecast' => ['Demand Forecasting', 'bi-graph-up-arrow'],
    ];

    $warnaKat   = ['Tinggi' => 'success', 'Sedang' => 'warning', 'Rendah' => 'secondary'];
    $warnaTingkat = ['kritis' => 'danger', 'tinggi' => 'danger', 'waspada' => 'warning', 'normal' => 'success'];
    $labelTingkat = ['kritis' => 'Kritis', 'tinggi' => 'Tinggi', 'waspada' => 'Waspada', 'normal' => 'Normal'];
    $warnaUrgensi = ['kritis' => 'danger', 'waspada' => 'warning', 'aman' => 'success'];

    // data grafik
    $rl = $ringkas['lead']; $ra = $ringkas['anomali']; $rf = $ringkas['forecast'];
    $grafLead = [$rl['tinggi'], $rl['sedang'], $rl['rendah']];
    $top10 = $data['anomali']->take(10);
    $grafAn = [
        'label' => $top10->map(fn ($l) => $l->detail['kode'] ?? '-')->values(),
        'z'     => $top10->map(fn ($l) => (float) ($l->detail['z'] ?? 0))->values(),
        'warna' => $top10->map(fn ($l) => ($l->detail['status'] ?? '') === 'anomali' ? '#f43f5e' : '#cbd5e1')->values(),
    ];
    $grafFc = [
        'label' => $data['forecast']->map(fn ($l) => $l->detail['nama'] ?? '-')->values(),
        'prediksi' => $data['forecast']->map(fn ($l) => $l->detail['prediksi'] ?? 0)->values(),
        'stok'  => $data['forecast']->map(fn ($l) => $l->detail['stok'] ?? 0)->values(),
        'min'   => $data['forecast']->map(fn ($l) => $l->detail['stok_minimum'] ?? 0)->values(),
    ];
@endphp

<ul class="nav nav-pills mb-4 gap-2">
    @foreach($tab as $t)
        <li class="nav-item">
            <button class="nav-link {{ $aktif == $t ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#t-{{ $t }}" type="button">
                <i class="bi {{ $judul[$t][1] }}"></i> {{ $judul[$t][0] }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">

{{-- ===================== LEAD SCORING ===================== --}}
@if(in_array('lead', $tab, true))
<div class="tab-pane fade {{ $aktif == 'lead' ? 'show active' : '' }}" id="t-lead">

    <div class="row g-3 mb-4">
        {!! $kartu('g-cyan',   'bi-person-lines-fill', 'Prospek Aktif',     $rl['total'], 'Prospect dan Negotiation') !!}
        {!! $kartu('g-green',  'bi-trophy-fill',       'Prioritas Tinggi',  $rl['tinggi'], $rl['tinggi'] ? $rp($rl['nilai_tinggi']) : 'belum ada') !!}
        {!! $kartu('g-violet', 'bi-speedometer2',      'Rata-rata Skor',    number_format($rl['rata'], 1), 'dari 100') !!}
        {!! $kartu('g-red',    'bi-snow2',             'Prospek Dingin',    $rl['dingin'], 'lebih dari ' . \App\Services\AiService::HARI_DINGIN . ' hari tanpa komunikasi') !!}
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                    <div>
                        <h6 class="mb-1">Ringkasan otomatis</h6>
                        <small class="text-muted">Dibuat dari hasil perhitungan terbaru, bukan keputusan akhir.</small>
                    </div>
                    <form method="POST" action="{{ route('ai.run', 'lead') }}" data-confirm="Hitung ulang skor seluruh prospek aktif?">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Hitung Skor</button>
                    </form>
                </div>
                @if($rl['insight'])
                    <div class="insight"><i class="bi bi-stars"></i> {{ $rl['insight'] }}</div>
                @else
                    <div class="insight text-muted">Belum ada hasil. Klik "Hitung Skor" untuk memulai.</div>
                @endif
                <div class="mt-4 small text-muted">
                    <b class="text-body">Cara membaca skor:</b>
                    skor = (anggaran ÷ Rp 100 juta × 60%) + (komunikasi ÷ 10 × 40%), maksimum 100.
                    <span class="chip chip-green">70–100 Tinggi</span>
                    <span class="chip chip-amber">40–69,99 Sedang</span>
                    <span class="chip chip-gray">0–39,99 Rendah</span>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100"><div class="card-header">Sebaran Prioritas</div>
                <div class="card-body d-flex justify-content-center align-items-center"><div style="max-width:220px;width:100%"><canvas id="gLead"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Prioritas Prospek</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>Prospek</th><th class="text-end">Anggaran</th><th>Komunikasi</th>
                    <th style="min-width:200px">Skor &amp; Penjelasan</th><th>Prioritas</th><th style="min-width:260px">Saran Tindakan</th>
                </tr></thead>
                <tbody>
                @forelse($data['lead'] as $l)
                    @php
                        $d  = $l->detail;
                        $ka = $d['kontribusi_anggaran']   ?? round(($d['skor_anggaran']   ?? 0) * 0.6, 1);
                        $kk = $d['kontribusi_komunikasi'] ?? round(($d['skor_komunikasi'] ?? 0) * 0.4, 1);
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ $link('lead.show', $l->ref_id) }}" class="fw-semibold text-decoration-none">{{ $d['nama'] ?? '-' }}</a>
                            <div><span class="chip chip-gray">{{ $d['status'] ?? 'prospect' }}</span></div>
                        </td>
                        <td class="text-end text-nowrap">{{ $rp($d['anggaran'] ?? 0) }}</td>
                        <td class="text-nowrap">
                            {{ $d['frekuensi'] ?? 0 }}x
                            <div class="small {{ !empty($d['dingin']) ? 'text-danger' : 'text-muted' }}">
                                @if(($d['hari_sejak'] ?? null) === null) belum pernah dihubungi
                                @else {{ $d['hari_sejak'] }} hari lalu @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <b class="fs-5">{{ number_format($l->skor, 1) }}</b>
                                <div class="sbar flex-grow-1" title="Anggaran {{ $ka }} + Komunikasi {{ $kk }}">
                                    <span class="bg-ind" style="width: {{ $ka }}%"></span><span class="bg-cyan" style="width: {{ $kk }}%"></span>
                                </div>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-circle-fill text-ind"></i> anggaran {{ $ka }}
                                <i class="bi bi-circle-fill text-cyan ms-2"></i> komunikasi {{ $kk }}
                            </div>
                        </td>
                        <td><span class="badge rounded-pill text-bg-{{ $warnaKat[$d['kategori'] ?? 'Rendah'] ?? 'secondary' }}">{{ $d['kategori'] ?? '-' }}</span></td>
                        <td class="small">{{ $d['tindakan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty"><i class="bi bi-bullseye"></i>Belum ada hasil. Klik "Hitung Skor".</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ===================== ANOMALI ===================== --}}
@if(in_array('anomali', $tab, true))
<div class="tab-pane fade {{ $aktif == 'anomali' ? 'show active' : '' }}" id="t-anomali">

    <div class="row g-3 mb-4">
        {!! $kartu('g-blue',  'bi-journal-check',      'Transaksi Diperiksa', $ra['total'], 'kelompok minimal ' . \App\Services\AiService::MIN_SAMPEL . ' data') !!}
        {!! $kartu('g-red',   'bi-shield-exclamation', 'Terindikasi Anomali', $ra['anomali'], $ra['kritis'] . ' kritis, ' . $ra['tinggi'] . ' tinggi') !!}
        {!! $kartu('g-amber', 'bi-cash-stack',         'Nilai Terindikasi',   $rp($ra['nominal']), 'total nominal anomali') !!}
        {!! $kartu('g-violet','bi-files',              'Kemungkinan Duplikat',$ra['duplikat'], 'nominal sama dalam ' . \App\Services\AiService::HARI_DUPLIKAT . ' hari') !!}
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                    <div>
                        <h6 class="mb-1">Ringkasan otomatis</h6>
                        <small class="text-muted">Metode Z-score per kelompok jenis dan kategori.</small>
                    </div>
                    <form method="POST" action="{{ route('ai.run', 'anomali') }}" data-confirm="Jalankan deteksi anomali pada seluruh transaksi?">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Jalankan Deteksi</button>
                    </form>
                </div>
                @if($ra['insight'])
                    <div class="insight"><i class="bi bi-stars"></i> {{ $ra['insight'] }}</div>
                @else
                    <div class="insight text-muted">Belum ada hasil. Klik "Jalankan Deteksi".</div>
                @endif
                <div class="mt-4 small text-muted">
                    <b class="text-body">Tingkat peringatan:</b>
                    <span class="chip chip-amber">Waspada |Z| ≥ 2</span>
                    <span class="chip chip-red">Tinggi |Z| ≥ 2,5</span>
                    <span class="chip chip-red">Kritis |Z| ≥ 3</span><br>
                    Z = (nominal − rata-rata kelompok) ÷ simpangan baku kelompok.
                </div>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100"><div class="card-header">Z-score Terbesar (10 transaksi)</div>
                <div class="card-body"><canvas id="gAn" height="170"></canvas></div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Hasil Pemeriksaan Transaksi</span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="hanyaAnomali">
                <label class="form-check-label small" for="hanyaAnomali">Tampilkan hanya yang terindikasi</label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="tbAnomali">
                <thead><tr>
                    <th>Transaksi</th><th>Kelompok</th><th class="text-end">Nominal</th>
                    <th>Dibanding Rata-rata</th><th style="min-width:150px">Z-score</th><th>Tingkat</th><th>Catatan</th>
                </tr></thead>
                <tbody>
                @forelse($data['anomali'] as $l)
                    @php
                        $d  = $l->detail;
                        $tg = $d['tingkat'] ?? (($d['status'] ?? '') === 'anomali' ? 'waspada' : 'normal');
                        $an = ($d['status'] ?? '') === 'anomali';
                        $z  = (float) ($d['z'] ?? 0);
                        $lebar = min(abs($z) / 4 * 100, 100);
                    @endphp
                    <tr class="{{ $an ? 'baris-anomali' : '' }}" data-an="{{ $an ? 1 : 0 }}">
                        <td class="text-nowrap"><b>{{ $d['kode'] ?? '-' }}</b><div class="small text-muted">{{ $tgl($d['tanggal'] ?? null) }}</div></td>
                        <td class="text-nowrap">{{ str_replace('|', ' · ', $d['kelompok'] ?? '-') }}</td>
                        <td class="text-end text-nowrap fw-semibold">{{ $rp($d['nominal'] ?? 0) }}</td>
                        <td class="small text-nowrap">
                            @if(isset($d['selisih_persen']))
                                <span class="{{ $d['selisih_persen'] >= 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                    {{ $d['selisih_persen'] >= 0 ? '+' : '' }}{{ number_format($d['selisih_persen'], 0, ',', '.') }}%
                                </span>
                            @endif
                            <div class="text-muted">rata-rata {{ $rp($d['rata_rata'] ?? 0) }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <b style="min-width:3rem">{{ number_format($z, 2) }}</b>
                                <div class="zmeter flex-grow-1 z-{{ $tg }}"><span style="width: {{ $lebar }}%"></span></div>
                            </div>
                        </td>
                        <td><span class="badge rounded-pill text-bg-{{ $warnaTingkat[$tg] ?? 'secondary' }}">{{ $labelTingkat[$tg] ?? $tg }}</span></td>
                        <td class="small">
                            @if(!empty($d['duplikat_dengan']))
                                <span class="chip chip-amber"><i class="bi bi-files"></i> mirip {{ $d['duplikat_dengan'] }}</span>
                            @elseif($an)
                                <span class="text-danger">Perlu diperiksa</span>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty"><i class="bi bi-shield-check"></i>Belum ada hasil. Klik "Jalankan Deteksi".</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ===================== FORECAST ===================== --}}
@if(in_array('forecast', $tab, true))
<div class="tab-pane fade {{ $aktif == 'forecast' ? 'show active' : '' }}" id="t-forecast">

    <div class="row g-3 mb-4">
        {!! $kartu('g-blue',  'bi-box-seam-fill',            'Barang Dianalisis', $rf['total'], 'punya riwayat barang keluar') !!}
        {!! $kartu('g-red',   'bi-exclamation-triangle-fill','Status Kritis',     $rf['kritis'], 'stok di bawah minimum atau habis dalam 7 hari') !!}
        {!! $kartu('g-amber', 'bi-cart-plus-fill',           'Perlu Dipesan',     $rf['perlu'], 'barang') !!}
        {!! $kartu('g-green', 'bi-boxes',                    'Total Rekomendasi', number_format($rf['unit'], 0, ',', '.'), 'unit untuk dipesan') !!}
    </div>

    <div class="card mb-4"><div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h6 class="mb-1">Ringkasan otomatis</h6>
                <small class="text-muted">Moving average 3 bulan terakhir sebelum bulan berjalan.</small>
            </div>
            <form method="POST" action="{{ route('ai.run', 'forecast') }}" data-confirm="Hitung ulang prediksi kebutuhan stok?">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Hitung Prediksi</button>
            </form>
        </div>
        @if($rf['insight'])
            <div class="insight"><i class="bi bi-stars"></i> {{ $rf['insight'] }}</div>
        @else
            <div class="insight text-muted">Belum ada hasil. Klik "Hitung Prediksi".</div>
        @endif
        <div class="mt-3 small text-muted">
            Prediksi = rata-rata pemakaian 3 bulan terakhir. Rekomendasi pesan = prediksi + stok minimum − stok saat ini (minimal 0).
            Sisa hari = stok ÷ (prediksi ÷ 30).
        </div>
    </div></div>

    <div class="card mb-4">
        <div class="card-header">Prediksi Kebutuhan, Stok, dan Stok Minimum</div>
        <div class="card-body"><canvas id="gFc" height="90"></canvas></div>
    </div>

    <div class="card">
        <div class="card-header">Rincian per Barang (yang paling mendesak di atas)</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>Barang</th><th>Pemakaian</th><th class="text-center">Prediksi</th><th>Tren</th>
                    <th class="text-center">Stok</th><th>Sisa Stok</th><th>Status</th><th class="text-center">Rekomendasi</th><th>Vendor</th><th></th>
                </tr></thead>
                <tbody>
                @forelse($data['forecast'] as $l)
                    @php $d = $l->detail; $ur = $d['urgensi'] ?? (($d['rekomendasi'] ?? 0) > 0 ? 'waspada' : 'aman'); @endphp
                    <tr>
                        <td class="text-nowrap"><b>{{ $d['nama'] ?? '-' }}</b><div class="small text-muted">{{ $d['kode'] ?? '' }}</div></td>
                        <td class="small text-muted text-nowrap">{{ implode(' · ', (array) ($d['pemakaian'] ?? [])) }}</td>
                        <td class="text-center fw-bold">{{ $d['prediksi'] ?? 0 }}</td>
                        <td class="text-nowrap small">
                            @php $tr = $d['tren'] ?? 'stabil'; @endphp
                            @if($tr === 'naik')   <span class="text-danger"><i class="bi bi-arrow-up-right"></i> naik {{ number_format($d['tren_persen'] ?? 0, 0) }}%</span>
                            @elseif($tr === 'turun') <span class="text-success"><i class="bi bi-arrow-down-right"></i> turun {{ number_format(abs($d['tren_persen'] ?? 0), 0) }}%</span>
                            @else <span class="text-muted"><i class="bi bi-dash"></i> stabil</span> @endif
                        </td>
                        <td class="text-center"><span class="badge text-bg-{{ ($d['stok'] ?? 0) <= ($d['stok_minimum'] ?? 0) ? 'danger' : 'success' }}">{{ $d['stok'] ?? 0 }}</span>
                            <div class="small text-muted">min {{ $d['stok_minimum'] ?? 0 }}</div></td>
                        <td class="small text-nowrap">
                            @if(($d['hari_tersisa'] ?? null) !== null)
                                ± {{ $d['hari_tersisa'] }} hari
                                <div class="text-muted">habis ± {{ $tgl($d['perkiraan_habis'] ?? null) }}</div>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                        <td><span class="badge rounded-pill text-bg-{{ $warnaUrgensi[$ur] ?? 'secondary' }}">{{ ucfirst($ur) }}</span></td>
                        <td class="text-center">
                            @if(($d['rekomendasi'] ?? 0) > 0)<span class="badge text-bg-warning">{{ $d['rekomendasi'] }} unit</span>
                            @else <span class="badge text-bg-success">Aman</span> @endif
                        </td>
                        <td class="small">{{ $d['vendor'] ?? '-' }}</td>
                        <td class="text-end text-nowrap">
                            @if(($d['rekomendasi'] ?? 0) > 0 && in_array($peran, ['admin', 'manajer_gudang'], true))
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('po.create', ['inventory_id' => $l->ref_id, 'jumlah' => $d['rekomendasi']]) }}">Buat PO</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10"><div class="empty"><i class="bi bi-graph-up-arrow"></i>Belum ada hasil. Klik "Hitung Prediksi".</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

</div>

<div class="card mt-4">
    <div class="card-header">Riwayat Perhitungan AI (15 terakhir)</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Waktu</th><th>Jenis</th><th>Data Sumber</th><th class="text-end">Skor</th></tr></thead>
            <tbody>
            @forelse($riwayat as $r)
                <tr>
                    <td class="text-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ str_replace('_', ' ', $r->jenis_analisis) }}</td>
                    <td>{{ $r->ref_tabel }} #{{ $r->ref_id }}</td>
                    <td class="text-end">{{ number_format($r->skor, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const dibuat = {};
    const D = {
        lead: @json($grafLead),
        an:   @json($grafAn),
        fc:   @json($grafFc)
    };

    function gambar(tab) {
        if (dibuat[tab] || typeof Chart === 'undefined') return;
        dibuat[tab] = true;

        if (tab === 'lead' && document.getElementById('gLead')) {
            new Chart(document.getElementById('gLead'), {
                type: 'doughnut',
                data: { labels: ['Tinggi', 'Sedang', 'Rendah'],
                        datasets: [{ data: D.lead, backgroundColor: ['#10b981', '#f59e0b', '#cbd5e1'], borderWidth: 0 }] },
                options: { cutout: '68%', plugins: { legend: { position: 'bottom' } } }
            });
        }
        if (tab === 'anomali' && document.getElementById('gAn')) {
            new Chart(document.getElementById('gAn'), {
                type: 'bar',
                data: { labels: D.an.label, datasets: [{ label: 'Z-score', data: D.an.z, backgroundColor: D.an.warna, borderRadius: 6 }] },
                options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { suggestedMin: 0, suggestedMax: 4 } } }
            });
        }
        if (tab === 'forecast' && document.getElementById('gFc')) {
            new Chart(document.getElementById('gFc'), {
                type: 'bar',
                data: { labels: D.fc.label, datasets: [
                    { label: 'Prediksi kebutuhan / bulan', data: D.fc.prediksi, backgroundColor: '#6366f1', borderRadius: 6 },
                    { label: 'Stok saat ini',              data: D.fc.stok,     backgroundColor: '#10b981', borderRadius: 6 },
                    { label: 'Stok minimum',               data: D.fc.min,      backgroundColor: '#f43f5e', borderRadius: 6 }
                ] },
                options: { plugins: { legend: { position: 'bottom' } } }
            });
        }
    }

    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(function (b) {
        b.addEventListener('shown.bs.tab', function (e) { gambar(e.target.dataset.bsTarget.replace('#t-', '')); });
    });
    gambar(@json($aktif));

    const saring = document.getElementById('hanyaAnomali');
    if (saring) {
        saring.addEventListener('change', function () {
            document.querySelectorAll('#tbAnomali tbody tr[data-an="0"]').forEach(function (tr) {
                tr.classList.toggle('d-none', saring.checked);
            });
        });
    }
})();
</script>
@endpush