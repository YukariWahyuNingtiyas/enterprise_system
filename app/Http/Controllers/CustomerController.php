<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private function rules(?Customer $c = null): array
    {
        return [
            'nama'    => 'required|string|min:3|max:100',
            'email'   => ['nullable', 'email', 'max:100', Rule::unique('crm_customers', 'email')->ignore($c?->id)],
            'telepon' => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'alamat'  => 'nullable|string|max:300',
        ];
    }

    public function index(Request $request)
    {
        $q = Customer::withCount('orders');
        if ($request->filled('cari')) {
            $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->cari.'%')
                                   ->orWhere('email', 'like', '%'.$request->cari.'%'));
        }
        $data = $q->orderBy('nama')->paginate(10)->withQueryString();
        return view('customer.index', compact('data'));
    }

    public function create() { return view('customer.create'); }

    public function store(Request $request)
    {
        Customer::create($request->validate($this->rules()));
        return redirect()->route('customer.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders' => fn ($q) => $q->latest('id'), 'lead']);
        return view('customer.show', compact('customer'));
    }

    public function edit(Customer $customer) { return view('customer.edit', compact('customer')); }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->validate($this->rules($customer)));
        return redirect()->route('customer.show', $customer)->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->orders()->exists() || $customer->invoices()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena sudah memiliki pesanan atau invoice.');
        }
        $customer->delete();
        return redirect()->route('customer.index')->with('success', 'Pelanggan dihapus.');
    }
}