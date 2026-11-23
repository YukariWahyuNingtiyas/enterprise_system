@extends('layouts.main')
@section('title', 'CRM · Riwayat Komunikasi')
@section('subtitle', 'Seluruh aktivitas komunikasi Sales dengan prospek')
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Cari nama prospek"></div>
    <div class="col-md-3"><select name="channel" class="form-select"><option value="">Semua jenis</option>
        @foreach(['telepon','email','whatsapp','tatap_muka'] as $c)<option value="{{ $c }}" @selected(request('channel')==$c)>{{ ucfirst(str_replace('_',' ',$c)) }}</option>@endforeach
    </select></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Tanggal</th><th>Prospek</th><th>Jenis</th><th>Catatan</th></tr></thead>
    <tbody>
    @forelse($data as $c)
        <tr><td>{{ $c->tanggal->format('d/m/Y') }}</td>
            <td><a href="{{ route('lead.show', $c->lead) }}" class="text-decoration-none fw-semibold">{{ $c->lead->nama }}</a></td>
            <td>{{ ucfirst(str_replace('_',' ',$c->channel)) }}</td><td>{{ $c->catatan ?: '-' }}</td></tr>
    @empty
        <tr><td colspan="4"><div class="empty"><i class="bi bi-chat-dots"></i>Belum ada riwayat komunikasi.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection