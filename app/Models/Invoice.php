<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'erp_invoices';
    protected $fillable = ['nomor_invoice','customer_id','tanggal_terbit','jatuh_tempo','total','status','catatan'];
    protected $casts = ['tanggal_terbit' => 'date', 'jatuh_tempo' => 'date'];

    public function customer() { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function items()    { return $this->hasMany(InvoiceItem::class, 'invoice_id'); }
}