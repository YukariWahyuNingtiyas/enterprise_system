<?php
namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $q = Journal::query();
        if ($request->filled('dari'))   $q->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai')) $q->whereDate('tanggal', '<=', $request->sampai);

        $total = (clone $q)->sum('nominal');
        $data  = $q->latest('tanggal')->latest('id')->paginate(15)->withQueryString();
        return view('journal.index', compact('data', 'total'));
    }
}