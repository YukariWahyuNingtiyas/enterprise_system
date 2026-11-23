<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'escm_inventory';
    protected $fillable = ['kode_barang','nama','stok','stok_minimum','harga_jual','lokasi_rak','vendor_id'];

    public function vendor()    { return $this->belongsTo(Vendor::class, 'vendor_id'); }
    public function movements() { return $this->hasMany(StockMovement::class, 'inventory_id'); }

    /** Stok menipis = stok <= batas minimal */
    public function scopeMenipis($q) { return $q->whereColumn('stok', '<=', 'stok_minimum'); }

    /** Jumlah pesan yang disarankan: sampai 2x batas minimal */
    public function getRekomendasiPesanAttribute(): int { return max(($this->stok_minimum * 2) - $this->stok, 1); }
}