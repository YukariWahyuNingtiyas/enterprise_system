<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    private function rules(?Inventory $i = null): array
    {
        $r = [
            'kode_barang'  => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/',
                               Rule::unique('escm_inventory', 'kode_barang')->ignore($i?->id)],
            'nama'         => 'required|string|min:3|max:100',
            'stok_minimum' => 'required|integer|min:0|max:1000000',
            'harga_jual'   => 'required|numeric|min:0|max:999999999999',
            'lokasi_rak'   => 'nullable|string|max:20',
            'vendor_id'    => 'nullable|exists:escm_vendors,id',
        ];
        if (!$i) $r['stok'] = 'required|integer|min:0|max:1000000';   // stok awal hanya saat barang baru dibuat
        return $r;
    }

    public function index(Request $request)
    {
        $q = Inventory::with('vendor');
        if ($request->filled('cari')) {
            $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->cari.'%')
                                   ->orWhere('kode_barang', 'like', '%'.$request->cari.'%'));
        }
        if ($request->boolean('menipis')) $q->menipis();

        $data    = $q->orderBy('kode_barang')->paginate(10)->withQueryString();
        $menipis = Inventory::menipis()->with('vendor')->get();
        return view('inventory.index', compact('data', 'menipis'));
    }

    public function create() { return view('inventory.form', ['vendors' => Vendor::orderBy('nama')->pluck('nama', 'id')]); }

    public function store(Request $request)
    {
        Inventory::create($request->validate($this->rules()));
        return redirect()->route('inventory.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Inventory $inventory)
    {
        return view('inventory.form', ['inventory' => $inventory, 'vendors' => Vendor::orderBy('nama')->pluck('nama', 'id')]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $inventory->update($request->validate($this->rules($inventory)));   // stok TIDAK diubah di sini
        return redirect()->route('inventory.index')->with('success', 'Data barang diperbarui.');
    }

    public function destroy(Inventory $inventory)
    {
        if ($inventory->movements()->exists() || PurchaseOrder::where('inventory_id', $inventory->id)->exists()
            || OrderItem::where('inventory_id', $inventory->id)->exists()) {
            return back()->with('error', 'Barang tidak dapat dihapus karena sudah memiliki riwayat stok, PO, atau pesanan.');
        }
        $inventory->delete();
        return back()->with('success', 'Barang dihapus.');
    }
}