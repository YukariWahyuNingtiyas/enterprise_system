<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::with('customer');
        // Gudang hanya melihat pesanan yang sudah dikonfirmasi
        if ($request->user()->hasRole('manajer_gudang')) $q->where('status', 'dikonfirmasi');
        if ($request->filled('pengiriman')) $q->where('status_pengiriman', $request->pengiriman);
        if ($request->filled('cari')) {
            $q->where(fn ($w) => $w->where('nomor_order', 'like', '%'.$request->cari.'%')
                ->orWhereHas('customer', fn ($c) => $c->where('nama', 'like', '%'.$request->cari.'%')));
        }
        $data = $q->latest('id')->paginate(10)->withQueryString();
        return view('order.index', compact('data'));
    }

    public function create(Request $request)
    {
        return view('order.create', [
            'customers'   => Customer::orderBy('nama')->pluck('nama', 'id'),
            'inventories' => Inventory::orderBy('nama')->get(),
            'customer_id' => $request->customer_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:crm_customers,id',
            'tanggal'               => 'required|date',
            'catatan'               => 'nullable|string|max:500',
            'items'                 => 'required|array|min:1',
            'items.*.inventory_id'  => 'required|exists:escm_inventory,id|distinct',
            'items.*.jumlah'        => 'required|integer|min:1|max:100000',
        ], [
            'items.required'               => 'Minimal satu barang harus dipilih.',
            'items.*.inventory_id.required'=> 'Pilih barang pada setiap baris.',
            'items.*.inventory_id.distinct'=> 'Barang yang sama tidak boleh dipilih dua kali.',
        ]);

        // Pemeriksaan awal ketersediaan stok (pemeriksaan final dilakukan lagi saat gudang memproses)
        $barang = Inventory::whereIn('id', collect($request->items)->pluck('inventory_id'))->get()->keyBy('id');
        foreach ($request->items as $i => $it) {
            $b = $barang[$it['inventory_id']];
            if ($it['jumlah'] > $b->stok) {
                throw ValidationException::withMessages(["items.$i.jumlah" => "Stok {$b->nama} hanya tersedia {$b->stok}."]);
            }
        }

        $order = DB::transaction(function () use ($request, $barang) {
            $total = 0;
            $baris = [];
            foreach ($request->items as $it) {
                $b = $barang[$it['inventory_id']];
                $sub = $it['jumlah'] * $b->harga_jual;          // harga diambil SERVER dari master barang
                $total += $sub;
                $baris[] = ['inventory_id' => $b->id, 'jumlah' => $it['jumlah'], 'harga' => $b->harga_jual, 'subtotal' => $sub];
            }
            $o = Order::create([
                'nomor_order' => 'ORD-'.date('Ym').'-'.strtoupper(substr(uniqid(), -5)),
                'customer_id' => $request->customer_id, 'tanggal' => $request->tanggal, 'total' => $total,
                'catatan' => $request->catatan, 'status' => 'draft', 'status_pengiriman' => 'menunggu',
                'created_by' => $request->user()->id,
            ]);
            $o->items()->createMany($baris);
            return $o;
        });

        return redirect()->route('order.show', $order)->with('success', 'Pesanan dibuat (draft). Silakan konfirmasi untuk menerbitkan invoice.');
    }

    public function show(Order $order)
    {
        $order->load('items.inventory', 'customer', 'invoice');
        return view('order.show', compact('order'));
    }

    public function konfirmasi(Request $request, Order $order)
    {
        try { OrderService::konfirmasi($order, $request->user()); }
        catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', 'Pesanan dikonfirmasi: invoice dan jurnal pendapatan telah dibuat otomatis.');
    }

    public function batal(Order $order)
    {
        if ($order->status !== 'draft') return back()->with('error', 'Hanya pesanan draft yang dapat dibatalkan.');
        $order->update(['status' => 'dibatalkan']);
        return back()->with('success', 'Pesanan dibatalkan.');
    }

    public function proses(Request $request, Order $order)
    {
        try { OrderService::proses($order, $request->user()); }
        catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', 'Pesanan diproses: stok berkurang. Jika stok menipis, PO draft dibuat otomatis.');
    }

    public function kirim(Request $request, Order $order)
    {
        $v = $request->validate(['no_resi' => 'required|string|min:5|max:50']);
        try { OrderService::ubahPengiriman($order, 'diproses', 'dikirim', $v['no_resi']); }
        catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', 'Pesanan ditandai dikirim.');
    }

    public function terima(Order $order)
    {
        try { OrderService::ubahPengiriman($order, 'dikirim', 'diterima'); }
        catch (RuntimeException $e) { return back()->with('error', $e->getMessage()); }
        return back()->with('success', 'Pesanan ditandai sudah diterima pelanggan.');
    }
}