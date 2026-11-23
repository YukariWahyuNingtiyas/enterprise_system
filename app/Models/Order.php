<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'crm_orders';
    protected $fillable = ['nomor_order','customer_id','invoice_id','status','status_pengiriman','no_resi','tanggal','total','catatan','created_by'];
    protected $casts = ['tanggal' => 'date'];

    public function customer() { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function items()    { return $this->hasMany(OrderItem::class, 'order_id'); }
    public function invoice()  { return $this->belongsTo(Invoice::class, 'invoice_id'); }
}