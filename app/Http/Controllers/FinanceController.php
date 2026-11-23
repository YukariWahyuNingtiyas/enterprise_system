<?php
namespace App\Http\Controllers;

use App\Models\Finance;
use Illuminate\Http\Request;
use App\Services\JurnalService; 
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    private function rules(): array
    {
        return [
            'jenis'     => 'required|in:pemasukan,pengeluaran',
            'kategori'  => ['required', 'string', 'min:3', 'max:50', 'regex:/^[\pL\s\-&]+$/u'],
            'nominal'   => 'required|numeric|min:1|max:999999999999',
            'tanggal'   => 'required|date|before_or_equal:today',
            'deskripsi' => 'nullable|string|max:500',
        ];
    }

    public function index(Request $request)
    {
        $q = Finance::query();
        if ($request->filled('dari'))     $q->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai'))   $q->whereDate('tanggal', '<=', $request->sampai);
        if ($request->filled('kategori')) $q->where('kategori', 'like', '%'.$request->kategori.'%');
        if ($request->filled('jenis'))    $q->where('jenis', $request->jenis);

        $totalMasuk  = (clone $q)->where('jenis', 'pemasukan')->sum('nominal');
        $totalKeluar = (clone $q)->where('jenis', 'pengeluaran')->sum('nominal');
        $data = $q->latest('tanggal')->latest('id')->paginate(10)->withQueryString();

        return view('finance.index', compact('data', 'totalMasuk', 'totalKeluar'));
    }

    public function create()
    {
        return view('finance.create');
    }

    public function store(Request $request)
    {
        $valid = $request->validate($this->rules());
        $valid['kode_transaksi'] = 'TRX-'.date('Ymd', strtotime($valid['tanggal'])).'-'.strtoupper(substr(uniqid(), -4));
        $valid['created_by'] = $request->user()->id;

        DB::transaction(function () use ($valid) {
            $f = Finance::create($valid);
            JurnalService::catat($f);
        });
        return redirect()->route('finance.index')->with('success', 'Transaksi dan jurnal berhasil disimpan.');
    }


    public function edit(Finance $finance)
    {
        return view('finance.edit', compact('finance'));
    }

    public function update(Request $request, Finance $finance)
    {
        if ($finance->invoice_id) {
            return back()->with('error', 'Transaksi dari invoice tidak boleh diubah manual.');
        }
        $valid = $request->validate($this->rules());
        DB::transaction(function () use ($finance, $valid) {
            $finance->update($valid);
            JurnalService::catat($finance->fresh());
        });
        return redirect()->route('finance.index')->with('success', 'Transaksi dan jurnal berhasil diubah.');
    }

    public function destroy(Finance $finance)
    {
        if ($finance->invoice_id) {
            return back()->with('error', 'Transaksi dari invoice tidak boleh dihapus manual.');
        }
        $finance->delete();
        return redirect()->route('finance.index')->with('success', 'Transaksi berhasil dihapus.');
    }
}