<?php

namespace App\Services;

use App\Models\AiLog;
use App\Models\Finance;
use App\Models\Inventory;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

/**
 * AI Service v2 — Smart Enterprise Ecosystem
 *
 * Rumus INTI tidak berubah dari v1 (jadi contoh hitungan manual di laporan tetap berlaku):
 *   - Lead Scoring   : (anggaran/100jt x 0,6 + komunikasi/10 x 0,4) x 100
 *   - Anomali        : Z-score per kelompok jenis+kategori, anomali jika |Z| >= 2
 *   - Forecasting    : moving average 3 bulan, rekomendasi = prediksi + stok minimum - stok
 *
 * Yang ditambahkan di v2 (tidak mengubah angka inti):
 *   - penjelasan skor (kontribusi tiap faktor), status "dingin", dan saran tindakan
 *   - tingkat keparahan anomali, selisih persen terhadap rata-rata, dan penanda kemungkinan duplikat
 *   - tren pemakaian, sisa hari stok, perkiraan tanggal habis, dan tingkat urgensi
 *
 * Fungsi hitung murni (tanpa database) dipisah agar mudah diuji dan dihitung manual.
 */
class AiService
{
    // ---- Parameter Lead Scoring ----
    const BOBOT_ANGGARAN   = 0.6;
    const BOBOT_KOMUNIKASI = 0.4;
    const BATAS_ANGGARAN   = 100000000;
    const BATAS_KOMUNIKASI = 10;
    const HARI_DINGIN      = 14;     // tanpa komunikasi > 14 hari dianggap dingin

    // ---- Parameter Anomali ----
    const Z_BATAS    = 2.0;          // |Z| >= 2   => anomali (waspada)
    const Z_TINGGI   = 2.5;          // |Z| >= 2,5 => tinggi
    const Z_KRITIS   = 3.0;          // |Z| >= 3   => kritis
    const MIN_SAMPEL = 5;
    const HARI_DUPLIKAT = 3;         // nominal + kategori sama dalam 3 hari => kemungkinan duplikat

    // ---- Parameter Forecasting ----
    const TREN_BATAS = 10;           // perubahan > 10% dianggap naik/turun

    // =====================================================================
    //  FUNGSI HITUNG MURNI (tanpa database)
    // =====================================================================

    /** Skor lead + kontribusi tiap faktor (kontribusi anggaran + kontribusi komunikasi = skor). */
    public static function skorLead(float $anggaran, int $komunikasi): array
    {
        $a = min($anggaran / self::BATAS_ANGGARAN, 1);
        $k = min($komunikasi / self::BATAS_KOMUNIKASI, 1);
        $skor = round(($a * self::BOBOT_ANGGARAN + $k * self::BOBOT_KOMUNIKASI) * 100, 2);

        return [
            'skor'      => $skor,
            'kategori'  => $skor >= 70 ? 'Tinggi' : ($skor >= 40 ? 'Sedang' : 'Rendah'),
            'a'         => $a,
            'k'         => $k,
            'kontribusi_anggaran'   => round($a * self::BOBOT_ANGGARAN * 100, 1),
            'kontribusi_komunikasi' => round($k * self::BOBOT_KOMUNIKASI * 100, 1),
        ];
    }

    /** Saran tindakan berbasis aturan (bukan keputusan otomatis). $hari = hari sejak komunikasi terakhir (null = belum pernah). */
    public static function saranLead(string $status, string $kategori, float $a, float $k, ?int $hari): string
    {
        if ($kategori === 'Tinggi') {
            $s = $status === 'negotiation'
                ? 'Segera tutup kesepakatan: jadwalkan negosiasi akhir dan kirim penawaran final.'
                : 'Prioritas utama: hubungi hari ini dan ajukan penawaran.';
        } elseif ($kategori === 'Sedang') {
            if ($a >= 0.5 && $k < 0.5)      $s = 'Anggaran besar tetapi komunikasi masih jarang: tambah intensitas komunikasi.';
            elseif ($k >= 0.5 && $a < 0.5)  $s = 'Komunikasi aktif tetapi anggaran kecil: tawarkan paket yang sesuai.';
            else                            $s = 'Lanjutkan tindak lanjut terjadwal minggu ini.';
        } else {
            $s = 'Prioritas rendah: pantau berkala dan kirim materi singkat.';
        }

        if ($hari === null)                    $s .= ' Belum pernah dihubungi, segera mulai komunikasi.';
        elseif ($hari > self::HARI_DINGIN)     $s .= " Sudah {$hari} hari tanpa komunikasi, segera hubungi kembali.";

        return $s;
    }

    /** Rata-rata dan simpangan baku POPULASI. */
    public static function statistik(array $nilai): array
    {
        $n = count($nilai);
        if ($n === 0) return ['rata' => 0.0, 'sd' => 0.0];
        $rata = array_sum($nilai) / $n;
        $var  = array_sum(array_map(fn ($v) => ($v - $rata) ** 2, $nilai)) / $n;
        return ['rata' => $rata, 'sd' => sqrt($var)];
    }

    /** normal | waspada | tinggi | kritis */
    public static function tingkatAnomali(float $z): string
    {
        $m = abs($z);
        if ($m >= self::Z_KRITIS)  return 'kritis';
        if ($m >= self::Z_TINGGI)  return 'tinggi';
        if ($m >= self::Z_BATAS)   return 'waspada';
        return 'normal';
    }

    /**
     * Peramalan satu barang.
     * @param array $pemakaianTerbaruDulu  total keluar per bulan, urutan TERBARU -> TERLAMA (maks 3 bulan)
     */
    public static function prediksiStok(array $pemakaianTerbaruDulu, int $stok, int $stokMinimum): array
    {
        $rata     = array_sum($pemakaianTerbaruDulu) / max(count($pemakaianTerbaruDulu), 1);
        $prediksi = (int) ceil($rata);
        $rekomendasi = max(0, $prediksi + $stokMinimum - $stok);

        // tren: bulan terakhir dibanding rata-rata 3 bulan
        $tren = 'stabil'; $trenPersen = 0.0;
        if (count($pemakaianTerbaruDulu) >= 2 && $rata > 0) {
            $trenPersen = round((($pemakaianTerbaruDulu[0] - $rata) / $rata) * 100, 1);
            if ($trenPersen > self::TREN_BATAS)       $tren = 'naik';
            elseif ($trenPersen < -self::TREN_BATAS)  $tren = 'turun';
        }

        // sisa hari = stok / pemakaian harian (prediksi per 30 hari)
        $hari = $prediksi > 0 ? (int) floor($stok / ($prediksi / 30)) : null;

        if ($stok <= $stokMinimum || ($hari !== null && $hari <= 7)) $urgensi = 'kritis';
        elseif ($rekomendasi > 0 || ($hari !== null && $hari <= 14)) $urgensi = 'waspada';
        else                                                         $urgensi = 'aman';

        return [
            'rata' => round($rata, 2), 'prediksi' => $prediksi, 'rekomendasi' => $rekomendasi,
            'tren' => $tren, 'tren_persen' => $trenPersen, 'hari_tersisa' => $hari, 'urgensi' => $urgensi,
        ];
    }

    private static function selisihHari(?string $tanggal): ?int
    {
        if (!$tanggal) return null;
        $a = strtotime(date('Y-m-d'));
        $b = strtotime(date('Y-m-d', strtotime($tanggal)));
        return (int) floor(($a - $b) / 86400);
    }

    // =====================================================================
    //  1) LEAD SCORING
    // =====================================================================
    public static function leadScoring(): int
    {
        $jumlah = 0;

        foreach (Lead::whereIn('status', ['prospect', 'negotiation'])->get() as $l) {
            $h = self::skorLead((float) $l->anggaran, (int) $l->frekuensi_komunikasi);

            $terakhir = DB::table('crm_communications')->where('lead_id', $l->id)->max('tanggal');
            $hari     = self::selisihHari($terakhir);
            $dingin   = $hari === null || $hari > self::HARI_DINGIN;

            DB::transaction(function () use ($l, $h, $terakhir, $hari, $dingin) {
                $l->update(['skor_prioritas' => $h['skor']]);
                AiLog::create([
                    'modul' => 'crm', 'jenis_analisis' => 'lead_scoring', 'ref_tabel' => 'crm_leads', 'ref_id' => $l->id,
                    'skor'  => $h['skor'],
                    'hasil' => json_encode([
                        'nama' => $l->nama, 'status' => $l->status, 'kategori' => $h['kategori'],
                        'anggaran' => (float) $l->anggaran, 'frekuensi' => (int) $l->frekuensi_komunikasi,
                        'skor_anggaran' => round($h['a'] * 100, 1), 'skor_komunikasi' => round($h['k'] * 100, 1),
                        'kontribusi_anggaran' => $h['kontribusi_anggaran'], 'kontribusi_komunikasi' => $h['kontribusi_komunikasi'],
                        'terakhir_komunikasi' => $terakhir, 'hari_sejak' => $hari, 'dingin' => $dingin,
                        'tindakan' => self::saranLead($l->status, $h['kategori'], $h['a'], $h['k'], $hari),
                    ]),
                ]);
            });
            $jumlah++;
        }
        return $jumlah;
    }

    // =====================================================================
    //  2) FINANCIAL ANOMALY DETECTION
    // =====================================================================
    public static function anomali(): array
    {
        $r = ['dievaluasi' => 0, 'anomali' => 0, 'kritis' => 0, 'duplikat' => 0, 'dilewati' => 0];

        // penanda kemungkinan duplikat: jenis + kategori + nominal sama, selisih <= 3 hari
        $duplikat = [];
        $sama = Finance::all()->groupBy(fn ($f) => $f->jenis . '|' . $f->kategori . '|' . (float) $f->nominal);
        foreach ($sama as $rows) {
            if ($rows->count() < 2) continue;
            $urut = $rows->sortBy('tanggal')->values();
            for ($i = 0; $i < $urut->count(); $i++) {
                for ($j = $i + 1; $j < $urut->count(); $j++) {
                    $selisih = abs(strtotime((string) $urut[$j]->tanggal) - strtotime((string) $urut[$i]->tanggal)) / 86400;
                    if ($selisih <= self::HARI_DUPLIKAT) {
                        $duplikat[$urut[$i]->id] = $urut[$j]->kode_transaksi;
                        $duplikat[$urut[$j]->id] = $urut[$i]->kode_transaksi;
                    }
                }
            }
        }

        $kelompok = Finance::all()->groupBy(fn ($f) => $f->jenis . '|' . $f->kategori);

        foreach ($kelompok as $nama => $rows) {
            if ($rows->count() < self::MIN_SAMPEL) { $r['dilewati']++; continue; }

            $nilai = $rows->pluck('nominal')->map(fn ($v) => (float) $v)->all();
            $st    = self::statistik($nilai);
            if ($st['sd'] == 0) { $r['dilewati']++; continue; }

            foreach ($rows as $f) {
                $nominal = (float) $f->nominal;
                $z       = ($nominal - $st['rata']) / $st['sd'];
                $tingkat = self::tingkatAnomali($z);
                $anomali = $tingkat !== 'normal';
                $dupl    = $duplikat[$f->id] ?? null;

                AiLog::create([
                    'modul' => 'erp', 'jenis_analisis' => 'anomali_keuangan', 'ref_tabel' => 'erp_finance', 'ref_id' => $f->id,
                    'skor'  => round($z, 4),
                    'hasil' => json_encode([
                        'status' => $anomali ? 'anomali' : 'normal', 'tingkat' => $tingkat,
                        'arah' => $z >= 0 ? 'di atas' : 'di bawah',
                        'kelompok' => $nama, 'kode' => $f->kode_transaksi, 'tanggal' => (string) $f->tanggal,
                        'nominal' => $nominal, 'rata_rata' => round($st['rata'], 2), 'simpang_baku' => round($st['sd'], 2),
                        'z' => round($z, 2),
                        'selisih_persen' => $st['rata'] > 0 ? round((($nominal - $st['rata']) / $st['rata']) * 100, 1) : null,
                        'duplikat_dengan' => $dupl,
                    ]),
                ]);
                $r['dievaluasi']++;
                if ($anomali) $r['anomali']++;
                if ($tingkat === 'kritis') $r['kritis']++;
                if ($dupl) $r['duplikat']++;
            }
        }
        return $r;
    }

    // =====================================================================
    //  3) DEMAND FORECASTING
    // =====================================================================
    public static function forecast(): array
    {
        $bulanIni = now()->format('Y-m');
        $r = ['dianalisis' => 0, 'dilewati' => 0, 'perlu_pesan' => 0, 'kritis' => 0];

        foreach (Inventory::with('vendor')->get() as $b) {
            $pakai = DB::table('escm_stock_movements')
                ->where('inventory_id', $b->id)->where('tipe', 'keluar')
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') < ?", [$bulanIni])
                ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(jumlah) as total")
                ->groupBy('bulan')->orderByDesc('bulan')->limit(3)->get();

            if ($pakai->isEmpty()) { $r['dilewati']++; continue; }

            $total = $pakai->pluck('total')->map(fn ($v) => (int) $v)->all();   // terbaru -> terlama
            $h     = self::prediksiStok($total, (int) $b->stok, (int) $b->stok_minimum);

            $habis = $h['hari_tersisa'] !== null ? date('Y-m-d', strtotime("+{$h['hari_tersisa']} days")) : null;

            AiLog::create([
                'modul' => 'escm', 'jenis_analisis' => 'prediksi_stok', 'ref_tabel' => 'escm_inventory', 'ref_id' => $b->id,
                'skor'  => $h['prediksi'],
                'hasil' => json_encode([
                    'nama' => $b->nama, 'kode' => $b->kode_barang,
                    'bulan' => $pakai->pluck('bulan')->reverse()->values(), 'pemakaian' => collect($total)->reverse()->values(),
                    'rata_rata' => $h['rata'], 'prediksi' => $h['prediksi'], 'stok' => (int) $b->stok, 'stok_minimum' => (int) $b->stok_minimum,
                    'rekomendasi' => $h['rekomendasi'], 'vendor' => $b->vendor->nama ?? null,
                    'tren' => $h['tren'], 'tren_persen' => $h['tren_persen'],
                    'hari_tersisa' => $h['hari_tersisa'], 'perkiraan_habis' => $habis, 'urgensi' => $h['urgensi'],
                    'status' => $h['rekomendasi'] > 0 ? 'perlu_pesan' : 'aman',
                ]),
            ]);
            $r['dianalisis']++;
            if ($h['rekomendasi'] > 0) $r['perlu_pesan']++;
            if ($h['urgensi'] === 'kritis') $r['kritis']++;
        }
        return $r;
    }

    /** Hasil terbaru per data sumber (riwayat lama tetap tersimpan di tabel) */
    public static function terbaru(string $jenis)
    {
        return AiLog::where('jenis_analisis', $jenis)
            ->whereIn('id', fn ($q) => $q->selectRaw('MAX(id)')->from('ai_analytics_logs')
                ->where('jenis_analisis', $jenis)->groupBy('ref_id'))
            ->get()
            ->map(function ($l) { $l->detail = json_decode($l->hasil, true) ?: []; return $l; });
    }
}