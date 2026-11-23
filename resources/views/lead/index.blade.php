@extends('layouts.main')
@section('title', 'CRM · Leads')
@section('subtitle', 'Data prospek dan skor prioritas hasil AI Lead Scoring')
@section('actions')
    @if(auth()->user()->hasRole('admin','sales'))
        <a href="{{ route('pipeline') }}" class="btn btn-outline-primary"><i class="bi bi-kanban"></i> Pipeline</a>
        <a href="{{ route('lead.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Lead</a>
    @endif
@endsection
@section('content')
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="cari" value="{{ request('cari') }}" class="form-control" placeholder="Cari nama / email"></div>
    <div class="col-md-3"><select name="status" class="form-select">
        <option value="">Semua status</option>
        @foreach(['prospect','negotiation','deal','lost'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach
    </select></div>
    <div class="col"><button class="btn btn-secondary"><i class="bi bi-search"></i> Cari</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama</th><th>Kontak</th><th class="text-end">Anggaran</th><th class="text-center">Komunikasi</th><th>Status</th><th>Skor AI</th><th></th></tr></thead>
    <tbody>
    @forelse($data as $l)
        <tr>
            <td><a class="fw-semibold text-decoration-none" href="{{ route('lead.show', $l) }}">{{ $l->nama }}</a>
                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($l->kebutuhan, 40) }}</div></td>
            <td>{{ $l->email }}<div class="small text-muted">{{ $l->telepon }}</div></td>
            <td class="text-end">@rp($l->anggaran)</td>
            <td class="text-center">{{ $l->frekuensi_komunikasi }}x</td>
            <td><x-badge :s="$l->status" /></td>
            <td>@if($l->prioritas)<span class="badge text-bg-{{ $l->prioritas[1] }}">{{ number_format($l->skor_prioritas, 1) }} · {{ $l->prioritas[0] }}</span>
                @else <span class="text-muted small">belum dihitung</span>@endif</td>
            <td class="text-end">
                <a href="{{ route('lead.show', $l) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                @if(auth()->user()->hasRole('admin','sales'))
                <a href="{{ route('lead.edit', $l) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('lead.destroy', $l) }}" method="POST" class="d-inline" data-confirm="Hapus prospek ini beserta riwayat komunikasinya?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i>Belum ada data prospek.</div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection