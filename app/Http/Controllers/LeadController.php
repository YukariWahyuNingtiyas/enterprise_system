<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    private function rules(): array
    {
        return [
            'nama'      => 'required|string|min:3|max:100',
            'email'     => 'nullable|email|max:100',
            'telepon'   => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'kebutuhan' => 'nullable|string|max:500',
            'anggaran'  => 'required|numeric|min:0|max:999999999999',
            'status'    => 'required|in:prospect,negotiation,deal,lost',
        ];
    }

    public function index(Request $request)
    {
        $q = Lead::query();
        if ($request->filled('cari')) {
            $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->cari.'%')
                                   ->orWhere('email', 'like', '%'.$request->cari.'%'));
        }
        if ($request->filled('status')) $q->where('status', $request->status);
        $data = $q->latest('id')->paginate(10)->withQueryString();
        return view('lead.index', compact('data'));
    }

    public function pipeline()
    {
        $data = Lead::orderByDesc('skor_prioritas')->get()->groupBy('status');
        return view('lead.pipeline', compact('data'));
    }

    public function create() { return view('lead.create'); }

    public function store(Request $request)
    {
        Lead::create($request->validate($this->rules()));
        return redirect()->route('lead.index')->with('success', 'Data prospek berhasil ditambahkan.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['communications' => fn ($q) => $q->latest('tanggal')->latest('id'), 'customer']);
        return view('lead.show', compact('lead'));
    }

    public function edit(Lead $lead) { return view('lead.edit', compact('lead')); }

    public function update(Request $request, Lead $lead)
    {
        $lead->update($request->validate($this->rules()));
        return redirect()->route('lead.show', $lead)->with('success', 'Data prospek diperbarui.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();      // riwayat komunikasi ikut terhapus (CASCADE); pelanggan hasil konversi tetap ada
        return redirect()->route('lead.index')->with('success', 'Data prospek dihapus.');
    }

    /** Mengubah tahap pipeline */
    public function status(Request $request, Lead $lead)
    {
        $request->validate(['status' => 'required|in:prospect,negotiation,deal,lost']);
        $lead->update(['status' => $request->status]);

        $pesan = 'Status prospek diubah menjadi '.ucfirst($request->status).'.';
        if ($request->status === 'deal' && !$lead->customer) $pesan .= ' Prospek siap dijadikan pelanggan.';
        return back()->with('success', $pesan);
    }

    /** Prospek berstatus Deal -> menjadi data pelanggan */
    public function convert(Lead $lead)
    {
        if ($lead->status !== 'deal')
            return back()->with('error', 'Hanya prospek berstatus Deal yang dapat dijadikan pelanggan.');
        if ($lead->customer)
            return back()->with('error', 'Prospek ini sudah menjadi pelanggan.');

        $c = Customer::create([
            'nama' => $lead->nama, 'email' => $lead->email, 'telepon' => $lead->telepon, 'lead_id' => $lead->id,
        ]);
        return redirect()->route('customer.show', $c)->with('success', 'Prospek berhasil dijadikan pelanggan.');
    }
}