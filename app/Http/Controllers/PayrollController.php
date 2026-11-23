<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $q = Payroll::with('employee');
        if ($request->filled('periode')) $q->where('periode', $request->periode);
        $data = $q->latest('periode')->latest('id')->paginate(10)->withQueryString();
        return view('payroll.index', compact('data'));
    }

    public function create()
    {
        return view('payroll.create', ['employees' => Employee::where('status', 'aktif')->orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'employee_id' => 'required|exists:erp_hr,id',
            'periode'     => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'gaji_pokok'  => 'required|numeric|min:0',
            'tunjangan'   => 'nullable|numeric|min:0',
            'potongan'    => 'nullable|numeric|min:0',
        ]);

        $v['tunjangan'] = $v['tunjangan'] ?? 0;
        $v['potongan']  = $v['potongan'] ?? 0;
        if ($v['potongan'] > $v['gaji_pokok'] + $v['tunjangan']) {
            return back()->withInput()->with('error', 'Potongan tidak boleh melebihi gaji pokok + tunjangan.');
        }

        if (Payroll::where('employee_id', $v['employee_id'])->where('periode', $v['periode'])->exists()) {
            return back()->withInput()->with('error', 'Gaji karyawan ini untuk periode tersebut sudah ada.');
        }

        $v['total_gaji'] = $v['gaji_pokok'] + $v['tunjangan'] - $v['potongan'];
        Payroll::create($v);

        return redirect()->route('payroll.index')->with('success', 'Data penggajian disimpan.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return back()->with('success', 'Data penggajian dihapus.');
    }
}