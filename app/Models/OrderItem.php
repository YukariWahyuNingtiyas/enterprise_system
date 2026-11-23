<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'crm_order_items';
    protected $fillable = ['order_id','inventory_id','jumlah','harga','subtotal'];
    public function inventory() { return $this->belongsTo(Inventory::class, 'inventory_id'); }
}