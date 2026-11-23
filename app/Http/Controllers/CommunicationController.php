<?php
namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Lead;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $q = Communication::with('lead');
        if ($request->filled('channel')) $q->where('channel', $request->channel);
        if ($request->filled('cari')) {
            $q->whereHas('lead', fn ($w) => $w->where('nama', 'like', '%'.$request->cari.'%'));
        }
        $data = $q->latest('tanggal')->latest('id')->paginate(10)->withQueryString();
        return view('communication.index', compact('data'));
    }

    public function store(Request $request, Lead $lead)
    {
        $v = $request->validate([
            'channel' => 'required|in:telepon,email,whatsapp,tatap_muka',
            'catatan' => 'nullable|string|max:500',
            'tanggal' => 'required|date|before_or_equal:today',
        ]);
        $lead->communications()->create($v);
        $lead->update(['frekuensi_komunikasi' => $lead->communications()->count()]);   // dipakai Lead Scoring
        return back()->with('success', 'Riwayat komunikasi dicatat.');
    }

    public function destroy(Communication $communication)
    {
        $lead = $communication->lead;
        $communication->delete();
        $lead->update(['frekuensi_komunikasi' => $lead->communications()->count()]);
        return back()->with('success', 'Riwayat komunikasi dihapus.');
    }
}