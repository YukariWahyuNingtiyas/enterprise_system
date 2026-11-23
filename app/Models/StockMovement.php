<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'escm_stock_movements';
    protected $fillable = ['inventory_id','tipe','jumlah','referensi','keterangan','tanggal','created_by'];
    protected $casts = ['tanggal' => 'date'];
    public function inventory() { return $this->belongsTo(Inventory::class, 'inventory_id'); }
    public function user()      { return $this->belongsTo(User::class, 'created_by'); }
}