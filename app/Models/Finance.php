<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $table = 'erp_finance';
    protected $fillable = ['kode_transaksi','invoice_id','jenis','kategori','nominal','tanggal','deskripsi','created_by'];
    protected $casts = ['tanggal' => 'date', 'nominal' => 'decimal:2'];
    public function journals() { return $this->hasMany(Journal::class, 'finance_id'); }
}
