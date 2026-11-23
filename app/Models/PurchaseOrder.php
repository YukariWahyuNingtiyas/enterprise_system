<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'escm_purchase_orders';
    protected $fillable = ['nomor_po','vendor_id','inventory_id','jumlah','status','tanggal'];
    protected $casts = ['tanggal' => 'date'];
    public function vendor()    { return $this->belongsTo(Vendor::class, 'vendor_id'); }
    public function inventory() { return $this->belongsTo(Inventory::class, 'inventory_id'); }
}