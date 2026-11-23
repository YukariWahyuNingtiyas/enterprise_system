<?php
namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    private function rules(): array
    {
        return [
            'nama'    => 'required|string|min:3|max:100',
            'alamat'  => 'nullable|string|max:300',
            'email'   => 'nullable|email|max:100',
            'telepon' => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
        ];
    }

    public function index(Request $request)
    {
        $q = Vendor::withCount('purchaseOrders');
        if ($request->filled('cari')) $q->where('nama', 'like', '%'.$request->cari.'%');
        $data = $q->orderBy('nama')->paginate(10)->withQueryString();
        return view('vendor.index', compact('data'));
    }

    public function create() { return view('vendor.form'); }

    public function store(Request $request)
    {
        Vendor::create($request->validate($this->rules()));
        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor) { return view('vendor.form', compact('vendor')); }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($request->validate($this->rules()));
        return redirect()->route('vendor.index')->with('success', 'Data vendor diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->purchaseOrders()->exists() || $vendor->inventories()->exists()) {
            return back()->with('error', 'Vendor tidak dapat dihapus karena masih terhubung dengan barang atau Purchase Order.');
        }
        $vendor->delete();
        return back()->with('success', 'Vendor dihapus.');
    }
}