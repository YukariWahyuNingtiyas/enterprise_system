<?php

namespace App\Http\Controllers;

use App\Models\AiLog;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    // tab => role yang boleh melihat & menjalankan
    private const AKSES = [
        'lead'     => ['admin', 'pimpinan', 'sales'],
        'anomali'  => ['admin', 'pimpinan', 'keuangan'],
        'forecast' => ['admin', 'pimpinan', 'manajer_gudang'],
    ];
    private const JENIS = ['lead' => 'lead_scoring', 'anomali' => 'anomali_keuangan', 'forecast' => 'prediksi_stok'];

    private function peran(): string
    {
        return auth()->user()->role->nama ?? '';
    }

    private function boleh(string $tab): bool
    {
        return in_array($this->peran(), self::AKSES[$tab], true);
    }

    private function rp($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    public function index()
    {
        $tab  = array_values(array_filter(array_keys(self::AKSES), fn ($t) => $this->boleh($t)));
        $data = ['lead' => collect(), 'anomali' => collect(), 'forecast' => collect()];
        foreach ($tab as $t) {
            $data[$t] = AiService::terbaru(self::JENIS[$t]);
        }

        $urut = ['kritis' => 0, 'waspada' => 1, 'aman' => 2];
        $data['lead']     = $data['lead']->sortByDesc('skor')->values();
        $data['anomali']  = $data['anomali']->sortByDesc(fn ($l) => abs((float) $l->skor))->values();
        $data['forecast'] = $data['forecast']->sortBy(
            fn ($l) => ($urut[$l->detail['urgensi'] ?? 'aman'] ?? 2) * 100000 - ($l->detail['rekomendasi'] ?? 0)
        )->values();

        $ringkas = [
            'lead'     => $this->ringkasLead($data['lead']),
            'anomali'  => $this->ringkasAnomali($data['anomali']),
            'forecast' => $this->ringkasForecast($data['forecast']),
        ];

        $riwayat = AiLog::whereIn('jenis_analisis', array_map(fn ($t) => self::JENIS[$t], $tab))
            ->latest('id')->take(15)->get();

        return view('ai.index', [
            'tab' => $tab, 'data' => $data, 'ringkas' => $ringkas, 'riwayat' => $riwayat, 'peran' => $this->peran(),
        ]);
    }

    // ------------------------- ringkasan & insight otomatis (berbasis aturan) -------------------------
    private function ringkasLead($c): array
    {
        $kat = fn ($k) => $c->filter(fn ($l) => ($l->detail['kategori'] ?? '') === $k);
        $tinggi = $kat('Tinggi');
        $dingin = $c->filter(fn ($l) => !empty($l->detail['dingin']))->count();
        $r = [
            'total' => $c->count(), 'tinggi' => $tinggi->count(), 'sedang' => $kat('Sedang')->count(), 'rendah' => $kat('Rendah')->count(),
            'rata'  => $c->count() ? round($c->avg('skor'), 1) : 0, 'dingin' => $dingin,
            'nilai_tinggi' => 0,
            'insight' => null,
        ];
        $r['nilai_tinggi'] = $tinggi->sum(fn ($l) => (float) ($l->detail['anggaran'] ?? 0));
        if ($c->count()) {
            $top = $c->first();
            $r['insight'] = "{$r['tinggi']} dari {$r['total']} prospek aktif berprioritas Tinggi"
                . ($r['tinggi'] ? ' dengan total anggaran ' . $this->rp($r['nilai_tinggi']) : '') . '. '
                . 'Prospek teratas: ' . ($top->detail['nama'] ?? '-') . ' (skor ' . number_format($top->skor, 1) . '). '
                . ($dingin ? "{$dingin} prospek sudah dingin (lebih dari " . AiService::HARI_DINGIN . ' hari tanpa komunikasi) dan perlu dihubungi kembali.' : 'Semua prospek masih aktif dihubungi.');
        }
        return $r;
    }

    private function ringkasAnomali($c): array
    {
        $an = $c->filter(fn ($l) => ($l->detail['status'] ?? '') === 'anomali');
        $r = [
            'total' => $c->count(), 'anomali' => $an->count(),
            'kritis' => $an->filter(fn ($l) => ($l->detail['tingkat'] ?? '') === 'kritis')->count(),
            'tinggi' => $an->filter(fn ($l) => ($l->detail['tingkat'] ?? '') === 'tinggi')->count(),
            'nominal' => $an->sum(fn ($l) => (float) ($l->detail['nominal'] ?? 0)),
            'duplikat' => $c->filter(fn ($l) => !empty($l->detail['duplikat_dengan']))->count(),
            'insight' => null,
        ];
        if ($c->count()) {
            $top = $an->first();
            $r['insight'] = "{$r['anomali']} dari {$r['total']} transaksi terindikasi anomali"
                . ($r['anomali'] ? " ({$r['kritis']} kritis, {$r['tinggi']} tinggi)" : '') . '. '
                . ($top ? 'Terbesar: ' . ($top->detail['kode'] ?? '-') . ' senilai ' . $this->rp($top->detail['nominal'] ?? 0)
                    . (isset($top->detail['selisih_persen']) ? ', ' . number_format(abs($top->detail['selisih_persen']), 0, ',', '.') . '% ' . ($top->detail['arah'] ?? '') . ' rata-rata kelompok. ' : '. ') : '')
                . 'Hasil ini penanda untuk diperiksa oleh Keuangan, bukan bukti bahwa transaksi salah.';
        }
        return $r;
    }

    private function ringkasForecast($c): array
    {
        $kritis = $c->filter(fn ($l) => ($l->detail['urgensi'] ?? '') === 'kritis');
        $perlu  = $c->filter(fn ($l) => ($l->detail['rekomendasi'] ?? 0) > 0);
        $r = [
            'total' => $c->count(), 'kritis' => $kritis->count(), 'perlu' => $perlu->count(),
            'unit' => (int) $perlu->sum(fn ($l) => $l->detail['rekomendasi'] ?? 0), 'insight' => null,
        ];
        if ($c->count()) {
            $top = $kritis->first() ?: $perlu->first();
            $r['insight'] = "{$r['kritis']} barang berstatus kritis dan {$r['perlu']} barang perlu dipesan (total {$r['unit']} unit)."
                . ($top ? ' Paling mendesak: ' . ($top->detail['nama'] ?? '-')
                    . (isset($top->detail['hari_tersisa']) ? ', perkiraan habis sekitar ' . $top->detail['hari_tersisa'] . ' hari lagi' : '') . '.' : ' Semua stok aman.');
        }
        return $r;
    }

    // ------------------------- menjalankan analisis -------------------------
    public function run(Request $request, string $jenis)
    {
        if ($jenis === 'semua') {
            abort_unless(in_array($this->peran(), ['admin', 'pimpinan'], true), 403, 'Anda tidak memiliki akses ke analisis ini.');
            $a = AiService::leadScoring();
            $b = AiService::anomali();
            $c = AiService::forecast();
            return redirect()->route('ai.index')->with('success',
                "Semua analisis selesai: {$a} prospek dinilai, {$b['anomali']} anomali ditemukan, {$c['dianalisis']} barang diramal.");
        }

        abort_unless(isset(self::AKSES[$jenis]) && $this->boleh($jenis), 403, 'Anda tidak memiliki akses ke analisis ini.');

        if ($jenis === 'lead') {
            $n = AiService::leadScoring();
            $pesan = "Lead Scoring selesai: {$n} prospek aktif dinilai.";
        } elseif ($jenis === 'anomali') {
            $r = AiService::anomali();
            $pesan = "Deteksi anomali selesai: {$r['dievaluasi']} transaksi diperiksa, {$r['anomali']} anomali (kritis {$r['kritis']}), "
                . "{$r['duplikat']} kemungkinan duplikat, {$r['dilewati']} kelompok dilewati karena data kurang dari " . AiService::MIN_SAMPEL . '.';
        } else {
            $r = AiService::forecast();
            $pesan = "Peramalan selesai: {$r['dianalisis']} barang diramal, {$r['perlu_pesan']} perlu dipesan, {$r['kritis']} kritis; "
                . "{$r['dilewati']} barang dilewati karena belum punya riwayat barang keluar.";
        }
        return redirect()->route('ai.index', ['tab' => $jenis])->with('success', $pesan);
    }
}