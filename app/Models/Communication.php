<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    protected $table = 'crm_communications';
    protected $fillable = ['lead_id','channel','catatan','tanggal'];
    protected $casts = ['tanggal' => 'date'];
    public function lead() { return $this->belongsTo(Lead::class, 'lead_id'); }
}