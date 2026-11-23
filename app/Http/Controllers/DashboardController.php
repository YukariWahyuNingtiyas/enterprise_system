<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Dashboard "tahan error".
 * Setiap bagian diambil terpisah. Jika satu bagian gagal (misalnya model CRM
 * belum dibuat), halaman TETAP terbuka dan pesan penyebabnya tampil di kotak
 * kuning di atas dashboard, sehingga mudah diperbaiki satu per satu.
 */
class DashboardController extends Controller
{
    private array $peringatan = [];

    private function aman(string $bagian, callable $fn, $bawaan)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e); // tercatat juga di storage/logs/laravel.log
            $this->peringatan[] = $bagian . ' → ' . class_basename($e) . ': ' . Str::limit($e->getMessage(), 180);
            return $bawaan;
        }
    }

    public function index()
    {
        $peran = auth()->user()->role->nama ?? '';

        $erp  = in_array($peran, ['admin', 'keuangan', 'pimpinan'], true);
        $crm  = in_array($peran, ['admin', 'sales', 'pimpinan'], true);
        $escm = in_array($peran, ['admin', 'manajer_gudang', 'pimpinan'], true);

        // Nilai awal semua variabel, supaya view tidak pernah "undefined variable"
        $d = [
            'erp' => $erp, 'crm' => $crm, 'escm' => $escm,
            'pemasukan' => 0, 'pengeluaran' => 0, 'jumlahInvoice' => 0, 'jumlahKaryawan' => 0,
            'periodeGaji' => null, 'totalGaji' => 0, 'transaksi' => collect(), 'anomali' => 0, 'grafik' => collect(),
            'totalLead' => 0, 'totalCustomer' => 0, 'pipeline' => collect(), 'leadPrioritas' => collect(), 'orderKirim' => collect(),
            'totalBarang' => 0, 'totalStok' => 0, 'menipis' => collect(), 'poAktif' => 0, 'poTerbaru' => collect(), 'pesananMasuk' => 0,
        ];

        $isi = function (string $bagian, string $kunci, callable $fn) use (&$d) {
            $d[$kunci] = $this->aman($bagian, $fn, $d[$kunci]);
        };

        // ---------------- ERP ----------------
        if ($erp) {
            $isi('ERP', 'pemasukan',      fn () => \App\Models\Finance::where('jenis', 'pemasukan')->sum('nominal'));
            $isi('ERP', 'pengeluaran',    fn () => \App\Models\Finance::where('jenis', 'pengeluaran')->sum('nominal'));
            $isi('ERP', 'jumlahInvoice',  fn () => \App\Models\Invoice::count());
            $isi('ERP', 'jumlahKaryawan', fn () => \App\Models\Employee::where('status', 'aktif')->count());
            $isi('ERP', 'periodeGaji',    fn () => \App\Models\Payroll::max('periode'));
            if ($d['periodeGaji']) {
                $isi('ERP', 'totalGaji',  fn () => \App\Models\Payroll::where('periode', $d['periodeGaji'])->sum('total_gaji'));
            }
            $isi('ERP', 'transaksi',      fn () => \App\Models\Finance::latest('tanggal')->latest('id')->take(5)->get());
            $isi('ERP', 'grafik',         fn () => \App\Models\Finance::select(
                    DB::raw("DATE_FORMAT(tanggal,'%Y-%m') as bulan"),
                    DB::raw("SUM(CASE WHEN jenis='pemasukan' THEN nominal ELSE 0 END) as masuk"),
                    DB::raw("SUM(CASE WHEN jenis='pengeluaran' THEN nominal ELSE 0 END) as keluar"))
                    ->groupBy('bulan')->orderBy('bulan')->get());
            $isi('AI anomali', 'anomali', fn () => \App\Services\AiService::terbaru('anomali_keuangan')
                    ->filter(fn ($l) => ($l->detail['status'] ?? '') === 'anomali')->count());
        }

        // ---------------- CRM ----------------
        if ($crm) {
            $isi('CRM', 'totalLead',     fn () => \App\Models\Lead::count());
            $isi('CRM', 'totalCustomer', fn () => \App\Models\Customer::count());
            $isi('CRM', 'pipeline',      fn () => \App\Models\Lead::select('status', DB::raw('COUNT(*) as n'))->groupBy('status')->pluck('n', 'status'));
            $isi('CRM', 'leadPrioritas', fn () => \App\Models\Lead::whereNotNull('skor_prioritas')
                    ->whereIn('status', ['prospect', 'negotiation'])->orderByDesc('skor_prioritas')->take(3)->get());
            $isi('CRM', 'orderKirim',    fn () => \App\Models\Order::with('customer')->where('status', 'dikonfirmasi')
                    ->whereIn('status_pengiriman', ['menunggu', 'diproses', 'dikirim'])->latest('id')->take(5)->get());
        }

        // ---------------- ESCM ----------------
        if ($escm) {
            $isi('ESCM', 'totalBarang',  fn () => \App\Models\Inventory::count());
            $isi('ESCM', 'totalStok',    fn () => \App\Models\Inventory::sum('stok'));
            $isi('ESCM', 'menipis',      fn () => \App\Models\Inventory::whereColumn('stok', '<=', 'stok_minimum')->orderBy('nama')->get());
            $isi('ESCM', 'poAktif',      fn () => \App\Models\PurchaseOrder::whereIn('status', ['draft', 'dipesan'])->count());
            $isi('ESCM', 'poTerbaru',    fn () => \App\Models\PurchaseOrder::with('inventory')->latest('id')->take(5)->get());
            $isi('ESCM', 'pesananMasuk', fn () => \App\Models\Order::where('status', 'dikonfirmasi')->where('status_pengiriman', 'menunggu')->count());
        }

        $d['peringatan'] = $this->peringatan;

        return view('dashboard.index', $d);
    }
}