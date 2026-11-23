<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'erp_invoice_items';
    protected $fillable = ['invoice_id','nama_item','jumlah','harga','subtotal'];
}