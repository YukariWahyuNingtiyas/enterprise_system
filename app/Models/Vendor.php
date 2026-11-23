<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'escm_vendors';
    protected $fillable = ['nama','alamat','email','telepon'];
    public function inventories()    { return $this->hasMany(Inventory::class, 'vendor_id'); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class, 'vendor_id'); }
}