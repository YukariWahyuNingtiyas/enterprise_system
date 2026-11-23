<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Finance;
use App\Models\Inventory;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    // status asal => status tujuan yang diizinkan
    private const ALUR = [
        'draft'       => ['diterbitkan', 'dibatalkan'],
        'diterbitkan' => ['lunas', 'jatuh_tempo', 'dibatalkan'],
        'jatuh_tempo' => ['lunas', 'dibatalkan'],
        'lunas'       => [],
        'dibatalkan'  => [],
    ];

    public function index(Request $request)
    {
        $q = Invoice::with('customer');
        if ($request->filled('status')) $q->where('status', $request->status);
        $data = $q->latest('id')->paginate(10)->withQueryString();
        return view('invoice.index', compact('data'));
    }

    public function create()
    {
        return view('invoice.create', [
            'customers'   => Customer::orderBy('nama')->get(),
            'inventories' => Inventory::orderBy('nama')->get(),   // daftar barang untuk dipilih
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:crm_customers,id',
            'tanggal_terbit' => 'required|date',
            'jatuh_tempo'    => 'required|date|after_or_equal:tanggal_terbit',
            'catatan'        => 'nullable|string|max:500',
            'items'                  => 'required|array|min:1',
            'items.*.inventory_id'   => 'required|exists:escm_inventory,id|distinct',
            'items.*.jumlah'         => 'required|integer|min:1',
        ], [
            'items.required'                => 'Tambahkan minimal satu item.',
            'items.*.inventory_id.required' => 'Pilih barang untuk setiap baris item.',
            'items.*.inventory_id.exists'   => 'Barang yang dipilih tidak ditemukan.',
            'items.*.inventory_id.distinct' => 'Barang yang sama tidak boleh dipilih dua kali.',
            'items.*.jumlah.required'       => 'Jumlah wajib diisi.',
            'items.*.jumlah.min'            => 'Jumlah minimal 1.',
        ]);

        // Nama dan harga diambil SERVER dari master barang (bukan dari input pengguna)
        $barang = Inventory::whereIn('id', collect($request->items)->pluck('inventory_id'))->get()->keyBy('id');

        // Jumlah tidak boleh melebihi stok yang tersedia
        foreach ($request->items as $i => $it) {
            $b = $barang[$it['inventory_id']];
            if ($it['jumlah'] > $b->stok) {
                throw ValidationException::withMessages(["items.$i.jumlah" => "Stok {$b->nama} hanya tersedia {$b->stok}."]);
            }
        }

        $invoice = DB::transaction(function () use ($request, $barang) {
            $baris = [];
            $total = 0;
            foreach ($request->items as $it) {
                $b   = $barang[$it['inventory_id']];
                $sub = $it['jumlah'] * $b->harga_jual;
                $total += $sub;
                $baris[] = ['nama_item' => $b->nama, 'jumlah' => $it['jumlah'], 'harga' => $b->harga_jual, 'subtotal' => $sub];
            }

            $invoice = Invoice::create([
                'nomor_invoice'  => 'INV-'.date('Ym').'-'.strtoupper(substr(uniqid(), -5)),
                'customer_id'    => $request->customer_id,
                'tanggal_terbit' => $request->tanggal_terbit,
                'jatuh_tempo'    => $request->jatuh_tempo,
                'total'          => $total,
                'status'         => 'draft',
                'catatan'        => $request->catatan,
            ]);

            foreach ($baris as $row) {
                $invoice->items()->create($row);
            }
            return $invoice;
        });

        return redirect()->route('invoice.show', $invoice)->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items');
        return view('invoice.show', compact('invoice'));
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:draft,diterbitkan,lunas,jatuh_tempo,dibatalkan']);
        $baru = $request->status;

        if (!in_array($baru, self::ALUR[$invoice->status] ?? [], true)) {
            return back()->with('error', "Status tidak boleh diubah dari {$invoice->status} ke {$baru}.");
        }

        DB::transaction(function () use ($invoice, $baru, $request) {
            $invoice->update(['status' => $baru]);

            // Integrasi ERP: invoice lunas -> otomatis jadi pemasukan di Finance (hanya sekali)
            if ($baru === 'lunas' && !Finance::where('invoice_id', $invoice->id)->exists()) {
                Finance::create([
                    'kode_transaksi' => 'TRX-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                    'invoice_id'     => $invoice->id,
                    'jenis'          => 'pemasukan',
                    'kategori'       => 'Penjualan',
                    'nominal'        => $invoice->total,
                    'tanggal'        => now()->toDateString(),
                    'deskripsi'      => 'Pembayaran invoice '.$invoice->nomor_invoice,
                    'created_by'     => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', 'Status invoice diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Hanya invoice berstatus draft yang boleh dihapus.');
        }
        $invoice->delete();   // item ikut terhapus (ON DELETE CASCADE)
        return redirect()->route('invoice.index')->with('success', 'Invoice dihapus.');
    }
}