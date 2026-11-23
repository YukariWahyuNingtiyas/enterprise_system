<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'crm_leads';
    protected $fillable = ['nama','email','telepon','kebutuhan','anggaran','frekuensi_komunikasi','status','skor_prioritas'];

    public function communications() { return $this->hasMany(Communication::class, 'lead_id'); }
    public function customer()       { return $this->hasOne(Customer::class, 'lead_id'); }

    /** [label, warna] berdasarkan skor AI; null bila belum dihitung */
    public function getPrioritasAttribute(): ?array
    {
        $s = $this->skor_prioritas;
        if ($s === null) return null;
        return $s >= 70 ? ['Tinggi', 'success'] : ($s >= 40 ? ['Sedang', 'warning'] : ['Rendah', 'secondary']);
    }
}