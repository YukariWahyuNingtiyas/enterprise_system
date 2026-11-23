<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'crm_customers';
    protected $fillable = ['nama','email','telepon','alamat','lead_id'];

    public function lead()     { return $this->belongsTo(Lead::class, 'lead_id'); }
    public function orders()   { return $this->hasMany(Order::class, 'customer_id'); }
    public function invoices() { return $this->hasMany(Invoice::class, 'customer_id'); }
}