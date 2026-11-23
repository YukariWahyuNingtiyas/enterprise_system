<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $q = StockMovement::with('inventory', 'user');
        if ($request->filled('tipe'))         $q->where('tipe', $request->tipe);
        if ($request->filled('inventory_id')) $q->where('inventory_id', $request->inventory_id);
        $data = $q->latest('tanggal')->latest('id')->paginate(15)->withQueryString();
        $barang = Inventory::orderBy('nama')->pluck('nama', 'id');
        return view('movement.index', compact('data', 'barang'));
    }

    public function create()
    {
        $barang = Inventory::orderBy('nama')->get()
            ->mapWithKeys(fn ($i) => [$i->id => "{$i->kode_barang} - {$i->nama} (stok {$i->stok})"]);
        return view('movement.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'inventory_id' => 'required|exists:escm_inventory,id',
            'tipe'         => 'required|in:masuk,keluar',
            'jumlah'       => 'required|integer|min:1|max:1000000',
            'tanggal'      => 'required|date|before_or_equal:today',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($v, $request) {
            $b = Inventory::lockForUpdate()->findOrFail($v['inventory_id']);

            if ($v['tipe'] === 'keluar' && $b->stok < $v['jumlah']) {
                throw ValidationException::withMessages(
                    ['jumlah' => "Stok {$b->nama} tidak cukup (tersedia {$b->stok})."]);
            }
            $v['tipe'] === 'masuk' ? $b->increment('stok', $v['jumlah']) : $b->decrement('stok', $v['jumlah']);

            StockMovement::create($v + ['referensi' => 'MANUAL', 'created_by' => $request->user()->id]);
        });

        return redirect()->route('movement.index')->with('success', 'Mutasi stok berhasil dicatat dan stok diperbarui.');
    }
}