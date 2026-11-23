<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'erp_hr';
    protected $fillable = ['kode_karyawan','nama','email','telepon','jabatan','bagian','tanggal_bergabung','status'];
    protected $casts = ['tanggal_bergabung' => 'date'];

    public function payrolls() { return $this->hasMany(Payroll::class, 'employee_id'); }
}