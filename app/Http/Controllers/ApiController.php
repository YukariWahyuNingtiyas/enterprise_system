<?php
namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use App\Services\AiService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ApiController extends Controller
{
    private const AI = [
        'lead'     => ['lead_scoring',     ['admin', 'pimpinan', 'sales']],
        'anomali'  => ['anomali_keuangan', ['admin', 'pimpinan', 'keuangan']],
        'forecast' => ['prediksi_stok',    ['admin', 'pimpinan', 'manajer_gudang']],
    ];

    private function izin(Request $r, array $roles): void
    {
        abort_unless($r->user()->hasRole(...$roles), 403, 'Role Anda tidak berhak mengakses endpoint ini.');
    }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        $u = User::where('email', $r->email)->first();
        if (!$u || !Hash::check($r->password, $u->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 422);
        }
        return response()->json(['token' => $u->createToken('api')->plainTextToken, 'role' => $u->role->nama ?? null]);
    }

    /** ESCM -> CRM : membaca status pesanan & pengiriman */
    public function order(Request $r, Order $order)
    {
        $this->izin($r, ['admin', 'sales', 'manajer_gudang', 'pimpinan']);
        return response()->json([
            'nomor_order' => $order->nomor_order, 'pelanggan' => $order->customer->nama, 'total' => (float) $order->total,
            'status' => $order->status, 'status_pengiriman' => $order->status_pengiriman, 'no_resi' => $order->no_resi,
        ]);
    }

    /** CRM -> ERP : konfirmasi pesanan -> invoice + jurnal */
    public function konfirmasi(Request $r, Order $order)
    {
        $this->izin($r, ['admin', 'sales']);
        try { OrderService::konfirmasi($order, $r->user()); }
        catch (RuntimeException $e) { return response()->json(['message' => $e->getMessage()], 409); }
        return response()->json(['message' => 'Pesanan dikonfirmasi.', 'invoice_id' => $order->fresh()->invoice_id]);
    }

    /** ERP -> ESCM : proses pesanan -> stok berkurang */
    public function proses(Request $r, Order $order)
    {
        $this->izin($r, ['admin', 'manajer_gudang']);
        try { OrderService::proses($order, $r->user()); }
        catch (RuntimeException $e) { return response()->json(['message' => $e->getMessage()], 409); }
        return response()->json(['message' => 'Pesanan diproses.', 'status_pengiriman' => $order->fresh()->status_pengiriman]);
    }

    public function lowStock(Request $r)
    {
        $this->izin($r, ['admin', 'manajer_gudang', 'pimpinan']);
        return Inventory::menipis()->get(['kode_barang', 'nama', 'stok', 'stok_minimum']);
    }

    public function financeSummary(Request $r)
    {
        $this->izin($r, ['admin', 'keuangan', 'pimpinan']);
        return response()->json([
            'pemasukan'   => (float) Finance::where('jenis', 'pemasukan')->sum('nominal'),
            'pengeluaran' => (float) Finance::where('jenis', 'pengeluaran')->sum('nominal'),
        ]);
    }

    public function ai(Request $r, string $jenis)
    {
        abort_unless(isset(self::AI[$jenis]), 404);
        $this->izin($r, self::AI[$jenis][1]);
        return AiService::terbaru(self::AI[$jenis][0])->map(fn ($l) => [
            'ref_tabel' => $l->ref_tabel, 'ref_id' => $l->ref_id, 'skor' => (float) $l->skor, 'hasil' => $l->detail,
        ])->values();
    }
}