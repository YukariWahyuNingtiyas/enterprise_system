<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'erp_payroll';
    protected $fillable = ['employee_id','periode','gaji_pokok','tunjangan','potongan','total_gaji'];

    public function employee() { return $this->belongsTo(Employee::class, 'employee_id'); }
}