<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private function rules(?int $id = null): array
    {
        return [
            'kode_karyawan'     => 'required|max:20|unique:erp_hr,kode_karyawan,'.$id,
            'nama'              => 'required|string|min:3|max:100',
            'email'             => 'required|email|max:100|unique:erp_hr,email,'.$id,
            'telepon'           => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'jabatan'           => 'required|string|max:50',
            'bagian'            => 'required|string|max:50',
            'tanggal_bergabung' => 'required|date',
            'status'            => 'required|in:aktif,nonaktif',
        ];
    }

    public function index(Request $request)
    {
        $q = Employee::query();
        if ($request->filled('cari')) {
            $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->cari.'%')
                                   ->orWhere('kode_karyawan', 'like', '%'.$request->cari.'%'));
        }
        $data = $q->orderBy('nama')->paginate(10)->withQueryString();
        return view('employee.index', compact('data'));
    }

    public function create()  { return view('employee.create'); }

    public function store(Request $request)
    {
        Employee::create($request->validate($this->rules()));
        return redirect()->route('employee.index')->with('success', 'Karyawan ditambahkan.');
    }

    public function edit(Employee $employee) { return view('employee.edit', compact('employee')); }

    public function update(Request $request, Employee $employee)
    {
        $employee->update($request->validate($this->rules($employee->id)));
        return redirect()->route('employee.index')->with('success', 'Data karyawan diubah.');
    }

    public function destroy(Employee $employee)
    {
        // Karyawan yang punya riwayat gaji tidak dihapus, hanya dinonaktifkan
        if ($employee->payrolls()->exists()) {
            $employee->update(['status' => 'nonaktif']);
            return back()->with('success', 'Karyawan memiliki riwayat gaji, sehingga dinonaktifkan.');
        }
        $employee->delete();
        return back()->with('success', 'Karyawan dihapus.');
    }
}