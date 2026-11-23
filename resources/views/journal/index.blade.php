@extends('layouts.main')
@section('title', 'ERP · Jurnal Akuntansi')
@section('subtitle', 'Pencatatan debit-kredit otomatis dari setiap transaksi keuangan')
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3"><input type="date" name="dari" value="{{ request('dari') }}" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control"></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Tanggal</th><th>Akun Debit</th><th>Akun Kredit</th><th class="text-end">Nominal</th><th>Keterangan</th></tr></thead>
    <tbody>
    @forelse($data as $j)
        <tr><td>{{ $j->tanggal->format('d/m/Y') }}</td><td>{{ $j->akun_debit }}</td><td>{{ $j->akun_kredit }}</td>
            <td class="text-end">@rp($j->nominal)</td><td class="text-muted">{{ $j->keterangan }}</td></tr>
    @empty
        <tr><td colspan="5"><div class="empty"><i class="bi bi-inbox"></i>Belum ada jurnal.</div></td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total (debit = kredit)</th><th class="text-end">@rp($total)</th><th></th></tr></tfoot>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection