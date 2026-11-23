<?php
namespace App\Services;

use App\Models\Finance;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    /** CRM -> ERP : pesanan dikonfirmasi => invoice + transaksi pendapatan + jurnal */
    public static function konfirmasi(Order $order, User $user): void
    {
        DB::transaction(function () use ($order, $user) {
            $o = Order::with('items.inventory')->lockForUpdate()->findOrFail($order->id);

            if ($o->status !== 'draft')     throw new RuntimeException('Pesanan sudah dikonfirmasi atau dibatalkan.');
            if ($o->items->isEmpty())       throw new RuntimeException('Pesanan tidak memiliki barang.');

            $invoice = Invoice::create([
                'nomor_invoice'  => 'INV-'.date('Ym').'-'.strtoupper(substr(uniqid(), -5)),
                'customer_id'    => $o->customer_id,
                'tanggal_terbit' => now()->toDateString(),
                'jatuh_tempo'    => now()->addDays(14)->toDateString(),
                'total'          => $o->total,
                'status'         => 'diterbitkan',
                'catatan'        => 'Dibuat otomatis dari pesanan '.$o->nomor_order,
            ]);
            foreach ($o->items as $it) {
                $invoice->items()->create([
                    'nama_item' => $it->inventory->nama, 'jumlah' => $it->jumlah,
                    'harga' => $it->harga, 'subtotal' => $it->subtotal,
                ]);
            }

            $fin = Finance::create([
                'kode_transaksi' => 'TRX-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4)),
                'invoice_id'     => $invoice->id,       // kolom UNIQUE => tidak mungkin tercatat dua kali
                'jenis'          => 'pemasukan',
                'kategori'       => 'Penjualan',
                'nominal'        => $o->total,
                'tanggal'        => now()->toDateString(),
                'deskripsi'      => 'Pendapatan pesanan '.$o->nomor_order,
                'created_by'     => $user->id,
            ]);
            JurnalService::catat($fin, 'Piutang Usaha');   // Debit Piutang Usaha, Kredit Pendapatan Penjualan

            $o->update(['status' => 'dikonfirmasi', 'invoice_id' => $invoice->id]);
        });
    }

    /** ERP -> ESCM : gudang memproses pesanan => stok berkurang + cek stok minimum */
    public static function proses(Order $order, User $user): void
    {
        DB::transaction(function () use ($order, $user) {
            $o = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if ($o->status !== 'dikonfirmasi' || $o->status_pengiriman !== 'menunggu')
                throw new RuntimeException('Pesanan tidak dalam status siap diproses.');

            foreach ($o->items as $it) {
                $b = Inventory::lockForUpdate()->findOrFail($it->inventory_id);
                if ($b->stok < $it->jumlah)
                    throw new RuntimeException("Stok {$b->nama} tidak cukup (tersedia {$b->stok}, dibutuhkan {$it->jumlah}).");

                $b->decrement('stok', $it->jumlah);
                StockMovement::create([
                    'inventory_id' => $b->id, 'tipe' => 'keluar', 'jumlah' => $it->jumlah,
                    'referensi' => $o->nomor_order, 'keterangan' => 'Pengeluaran untuk pesanan pelanggan',
                    'tanggal' => now()->toDateString(), 'created_by' => $user->id,
                ]);
                self::cekStokMenipis($b->fresh());
            }
            $o->update(['status_pengiriman' => 'diproses']);
        });
    }

    /** Jika stok <= minimum dan belum ada PO aktif => buat PO draft ke vendor utama barang */
    private static function cekStokMenipis(Inventory $b): void
    {
        if ($b->stok > $b->stok_minimum || !$b->vendor_id) return;
        $sudahAda = PurchaseOrder::where('inventory_id', $b->id)->whereIn('status', ['draft', 'dipesan'])->exists();
        if ($sudahAda) return;

        PurchaseOrder::create([
            'nomor_po' => 'PO-'.date('Ym').'-'.strtoupper(substr(uniqid(), -5)),
            'vendor_id' => $b->vendor_id, 'inventory_id' => $b->id,
            'jumlah' => $b->rekomendasi_pesan, 'status' => 'draft', 'tanggal' => now()->toDateString(),
        ]);
    }

    /** ESCM -> CRM : perubahan status pengiriman (diproses -> dikirim -> diterima) */
    public static function ubahPengiriman(Order $order, string $dari, string $ke, ?string $resi = null): void
    {
        DB::transaction(function () use ($order, $dari, $ke, $resi) {
            $o = Order::lockForUpdate()->findOrFail($order->id);
            if ($o->status !== 'dikonfirmasi' || $o->status_pengiriman !== $dari)
                throw new RuntimeException("Tindakan ini hanya untuk pesanan berstatus pengiriman '{$dari}'.");
            $o->update(['status_pengiriman' => $ke] + ($resi ? ['no_resi' => $resi] : []));
        });
    }
}