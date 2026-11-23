<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private const ALUR = [
        'draft'      => ['dipesan', 'dibatalkan'],
        'dipesan'    => ['diterima', 'dibatalkan'],
        'diterima'   => [],
        'dibatalkan' => [],
    ];

    public function index(Request $request)
    {
        $q = PurchaseOrder::with('vendor', 'inventory');
        if ($request->filled('status')) $q->where('status', $request->status);
        $data = $q->latest('id')->paginate(10)->withQueryString();
        return view('po.index', compact('data'));
    }

    public function create(Request $request)
    {
        $barang = Inventory::orderBy('nama')->get();
        $dipilih = $barang->firstWhere('id', (int) $request->inventory_id);
        return view('po.create', [
            'vendors'  => Vendor::orderBy('nama')->pluck('nama', 'id'),
            'barang'   => $barang->mapWithKeys(fn ($i) => [$i->id => "{$i->kode_barang} - {$i->nama} (stok {$i->stok})"]),
            'inventory_id' => $request->inventory_id,
            'vendor_id'    => $dipilih->vendor_id ?? null,
            'jumlah'       => $request->jumlah ?: 1,
        ]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'vendor_id'    => 'required|exists:escm_vendors,id',
            'inventory_id' => 'required|exists:escm_inventory,id',
            'jumlah'       => 'required|integer|min:1|max:100000',
            'tanggal'      => 'required|date',
        ]);
        $v['nomor_po'] = 'PO-'.date('Ym').'-'.strtoupper(substr(uniqid(), -5));
        $v['status'] = 'draft';
        PurchaseOrder::create($v);
        return redirect()->route('po.index')->with('success', 'Purchase Order berhasil dibuat (status draft).');
    }

    public function updateStatus(Request $request, PurchaseOrder $po)
    {
        $request->validate(['status' => 'required|in:dipesan,diterima,dibatalkan']);
        $baru = $request->status;

        if (!in_array($baru, self::ALUR[$po->status] ?? [], true)) {
            return back()->with('error', "Status PO tidak boleh diubah dari {$po->status} ke {$baru}.");
        }

        DB::transaction(function () use ($po, $baru, $request) {
            $p = PurchaseOrder::lockForUpdate()->findOrFail($po->id);
            if (!in_array($baru, self::ALUR[$p->status] ?? [], true)) return;   // sudah diproses pihak lain

            $p->update(['status' => $baru]);

            // Barang diterima -> stok BERTAMBAH (hanya sekali, karena status 'diterima' tidak bisa diubah lagi)
            if ($baru === 'diterima') {
                Inventory::lockForUpdate()->findOrFail($p->inventory_id)->increment('stok', $p->jumlah);
                StockMovement::create([
                    'inventory_id' => $p->inventory_id, 'tipe' => 'masuk', 'jumlah' => $p->jumlah,
                    'referensi' => $p->nomor_po, 'keterangan' => 'Penerimaan barang dari PO',
                    'tanggal' => now()->toDateString(), 'created_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', 'Status PO diperbarui.');
    }

    public function destroy(PurchaseOrder $po)
    {
        if ($po->status !== 'draft') return back()->with('error', 'Hanya PO berstatus draft yang dapat dihapus.');
        $po->delete();
        return back()->with('success', 'Purchase Order dihapus.');
    }
}