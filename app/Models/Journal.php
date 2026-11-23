<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $table = 'erp_journals';
    protected $fillable = ['finance_id','akun_debit','akun_kredit','nominal','tanggal','keterangan'];
    protected $casts = ['tanggal' => 'date'];
    public function finance() { return $this->belongsTo(Finance::class, 'finance_id'); }
}