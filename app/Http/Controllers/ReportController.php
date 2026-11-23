<?php
namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Invoice;
use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Models\{Communication, Inventory, Lead, Order, PurchaseOrder, StockMovement}; 
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dari   = $request->input('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());

        $transaksi = Finance::whereBetween('tanggal', [$dari, $sampai])->orderBy('tanggal')->get();
        $masuk  = $transaksi->where('jenis', 'pemasukan')->sum('nominal');
        $keluar = $transaksi->where('jenis', 'pengeluaran')->sum('nominal');

        $invoice = Invoice::with('customer')->whereBetween('tanggal_terbit', [$dari, $sampai])->get();
        $gaji = Payroll::with('employee')
                ->whereBetween('periode', [substr($dari, 0, 7), substr($sampai, 0, 7)])->get();


        $perKategori = $transaksi->groupBy(fn ($t) => $t->jenis.'|'.$t->kategori)
            ->map(fn ($g) => ['jenis' => $g->first()->jenis, 'kategori' => $g->first()->kategori,
                        'jumlah' => $g->count(), 'total' => $g->sum('nominal')])
            ->sortByDesc('total')->values();
        
        return view('report.index', compact('dari', 'sampai', 'transaksi', 'masuk', 'keluar', 'invoice', 'gaji', 'perKategori'));
    }
    public function crm()
    {
        return view('report.crm', [
            'pipeline'   => Lead::select('status', DB::raw('COUNT(*) as n'), DB::raw('SUM(anggaran) as total'))->groupBy('status')->get(),
            'komunikasi' => Communication::select('channel', DB::raw('COUNT(*) as n'))->groupBy('channel')->get(),
            'topLead'    => Lead::whereNotNull('skor_prioritas')->orderByDesc('skor_prioritas')->take(5)->get(),
            'pengiriman' => Order::select('status_pengiriman', DB::raw('COUNT(*) as n'))->where('status', 'dikonfirmasi')->groupBy('status_pengiriman')->get(),
        ]);
    }

    public function escm()
    {
        return view('report.escm', [
            'stok'   => Inventory::with('vendor')->orderBy('nama')->get(),
            'mutasi' => StockMovement::select(DB::raw("DATE_FORMAT(tanggal,'%Y-%m') as bulan"), 'tipe', DB::raw('SUM(jumlah) as total'))
                            ->groupBy('bulan', 'tipe')->orderBy('bulan')->get(),
            'po'     => PurchaseOrder::select('status', DB::raw('COUNT(*) as n'))->groupBy('status')->get(),
        ]);
    }
}